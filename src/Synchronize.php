<?php
namespace DasRed\PhraseApp;

use DasRed\PhraseApp\Synchronize\Exception\FailureAddKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureDeleteKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureAddLocale;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContent;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContentByTag;
use DasRed\Zend\Log\LoggerAwareTrait;
use Zend\Log\Logger;
use Zend\Log\LoggerAwareInterface;
use DasRed\PhraseApp\Request\KeysAwareInterface;
use DasRed\PhraseApp\Request\LocalesAwareInterface;
use DasRed\PhraseApp\Request\TranslationsAwareInterface;
use DasRed\PhraseApp\Request\KeysAwareTrait;
use DasRed\PhraseApp\Request\LocalesAwareTrait;
use DasRed\PhraseApp\Request\TranslationsAwareTrait;

class Synchronize implements LoggerAwareInterface, ConfigAwareInterface, KeysAwareInterface, LocalesAwareInterface, TranslationsAwareInterface
{
	use LoggerAwareTrait;
	use ConfigAwareTrait;
	use KeysAwareTrait;
	use LocalesAwareTrait;
	use TranslationsAwareTrait;

	/**
	 * current translations with [LOCALE => [KEY => CONTENT]]
	 *
	 * @var array
	 */
	protected $translations = [];

	/**
	 *
	 * @param Logger $logger
	 * @param Config $config
	 */
	public function __construct(Logger $logger, Config $config)
	{
		$this->setLogger($logger)->setConfig($config);
	}

	/**
	 *
	 * @param string $locale
	 * @param string $key
	 * @param string $content
	 * @return self
	 */
	public function addTranslation($locale, $key, $content)
	{
		if (array_key_exists($locale, $this->translations) === false)
		{
			$this->translations[$locale] = [];
		}

		$this->translations[$locale][$key] = $content;

		return $this;
	}

	/**
	 *
	 * @param string $locale
	 * @param array $translations key value list
	 * @return self
	 */
	public function addTranslations($locale, array $translations)
	{
		foreach ($translations as $key => $content)
		{
			$this->addTranslation($locale, $key, $content);
		}

		return $this;
	}

	/**
	 *
	 * @param string $locale
	 * @param string $key
	 * @return string
	 */
	public function getTranslation($locale, $key)
	{
		if (array_key_exists($locale, $this->translations) === false)
		{
			return null;
		}

		if (array_key_exists($key, $this->translations[$locale]) === false)
		{
			return null;
		}

		return $this->translations[$locale][$key];
	}

	/**
	 *
	 * @return array
	 */
	protected function getTranslationLocales()
	{
		$result = array_keys($this->translations);

		// sorting the locales so that the main locale is the first
		usort($result, function ($localeA, $localeB)
		{
			if ($localeA === $this->getConfig()->getLocaleDefault())
			{
				return -1;
			}
			if ($localeB === $this->getConfig()->getLocaleDefault())
			{
				return 1;
			}

			return 0;
		});

		return $result;
	}

	/**
	 *
	 * @return array
	 */
	protected function getKeys($locale = null)
	{
		if ($locale === null)
		{
			$result = [];
			foreach ($this->getTranslationLocales() as $locale)
			{
				$result = array_merge($result, $this->getKeys($locale));
			}

			return array_values(array_unique($result));
		}

		if (array_key_exists($locale, $this->translations) === false)
		{
			return [];
		}

		return array_keys($this->translations[$locale]);
	}

	/**
	 *
	 * @param string $locale
	 * @return array
	 */
	public function getTranslations($locale = null)
	{
		if ($locale === null)
		{
			return $this->translations;
		}

		if (array_key_exists($locale, $this->translations) === false)
		{
			return [];
		}

		return $this->translations[$locale];
	}

	/**
	 *
	 * @param string $key
	 * @return self
	 */
	protected function removeTranslationKeyFromAllLocales($key)
	{
		foreach (array_keys($this->translations) as $locale)
		{
			if (array_key_exists($key, $this->translations[$locale]) === true)
			{
				unset($this->translations[$locale][$key]);
			}
		}

		return $this;
	}

	/**
	 *
	 * @return self
	 */
	public function synchronize()
	{
		$this->log('Synchronizing translations');

		// sync translation content
		return $this->synchronizeLocales()->synchronizeKeys()->synchronizeCleanUpKeys()->synchronizeContent();
	}

	/**
	 *
	 * @return self
	 */
	protected function synchronizeCleanUpKeys()
	{
		// clean up the keys in all other locales other then the main locale
		$translationsForMainLocale = $this->getTranslations($this->getConfig()->getLocaleDefault());
		foreach ($this->getTranslationLocales() as $locale)
		{
			if ($locale === $this->getConfig()->getLocaleDefault())
			{
				continue;
			}

			foreach ($this->getKeys($locale) as $keyToDelete)
			{
				if (array_key_exists($keyToDelete, $translationsForMainLocale) === false)
				{
					$this->removeTranslationKeyFromAllLocales($keyToDelete);
				}
			}
		}

		return $this;
	}

	/**
	 *
	 * @return self
	 */
	protected function synchronizeContent()
	{
		$locales = $this->getTranslationLocales();
		$countPerLocale = count($this->getKeys($this->getConfig()->getLocaleDefault()));
		$count = count($locales) * $countPerLocale;

		$this->log('Fetching full translation contents');
		$contentRemoteCompleteByLocale = $this->getPhraseAppTranslations()->fetch();

		$this->log('Updating translation contents');
		$countDifferencesLocal = 0;
		$countDifferencesRemote = 0;

		foreach ($locales as $locale)
		{
			$contentLocale = $this->getTranslations($locale);
			$contentRemote = array_key_exists($locale, $contentRemoteCompleteByLocale) ? $contentRemoteCompleteByLocale[$locale] : [];
			$keysLocalToStore = [];

			// prefer remote
			if ($this->getConfig()->getPreferDirection() === Config::PREFER_REMOTE)
			{
				// overwrite all from remote and send only new content from local to remote
				$countDifferencesLocal += count(array_diff($contentRemote, $contentLocale));
				$this->translations[$locale] = array_merge($contentLocale, $contentRemote);
				$keysLocalToStore = array_diff(array_keys($contentLocale), array_keys($contentRemote));
			}
			// prefer local content... no changes on local content!
			elseif ($this->getConfig()->getPreferDirection() === Config::PREFER_LOCAL)
			{
				// find differences between local and remote and send local to remote
				foreach ($contentLocale as $key => $content)
				{
					// local key does not exists on remote... send local
					if (array_key_exists($key, $contentRemote) === false)
					{
						$keysLocalToStore[] = $key;
					}
					// local content is different to remote content... send local
					elseif ($content !== $contentRemote[$key])
					{
						$keysLocalToStore[] = $key;
					}
				}
			}

			// find content to send
			$countLocalToStore = count($keysLocalToStore);
			$countDifferencesRemote += $countLocalToStore;
			if ($countLocalToStore != 0)
			{
				$count = count($keysLocalToStore);
				$index = 0;
				$this->log($locale . ': ' . $index . ' / ' . $count, Logger::DEBUG, [
					'writeLine' => false
				]);
				foreach ($keysLocalToStore as $keyLocalToStore)
				{
					// add the "new" tag to key if new in defaultlocale
					if ($this->getConfig()->getTagForContentChangeFromLocalToRemote() !== null && $this->getConfig()->getLocaleDefault() === $locale && $this->getPhraseAppKeys()->addTag($keyLocalToStore, $this->getConfig()->getTagForContentChangeFromLocalToRemote()) === false)
					{
						throw new FailureStoreContentByTag($keyLocalToStore);
					}

					// store content remote
					$content = $contentLocale[$keyLocalToStore];
					if ($this->getPhraseAppTranslations()->store($locale, $keyLocalToStore, $content) === false)
					{
						throw new FailureStoreContent($keyLocalToStore);
					}

					// write to log
					$this->log(str_repeat(chr(8), strlen($index . ' / ' . $count)), Logger::DEBUG, [
						'writeLine' => false
					]);
					$index++;
					$this->log($index . ' / ' . $count, Logger::DEBUG, [
						'writeLine' => false
					]);
				}
				$this->log(' Done');
			}
		}

		$this->log('Found ' . number_format($countDifferencesLocal, 0, ',', '.') . ' local and ' . number_format($countDifferencesRemote, 0, ',', '.') . ' remote differences');

		return $this;
	}

	/**
	 *
	 * @return self
	 */
	protected function synchronizeKeys()
	{
		// collect keys given
		$keysLocal = array_keys($this->getTranslations($this->getConfig()->getLocaleDefault()));

		// fetching the list of current translation keys in PhraseApp
		$this->log('Fetching keys from PhraseApp');
		$keysRemote = $this->getPhraseAppKeys()->fetch();

		// find keys for sync
		$keysToCreate = array_diff($keysLocal, $keysRemote);
		$keysToDelete = array_diff($keysRemote, $keysLocal);

		// create keys
		$count = count($keysToCreate);
		if ($count != 0)
		{
			$index = 0;
			$this->log('Found ' . $count . ' keys to create: ' . $index . ' / ' . $count, Logger::DEBUG, [
				'writeLine' => false
			]);
			foreach ($keysToCreate as $keyToCreate)
			{
				$this->synchronizeKeysCreateKey($keyToCreate);

				// write to log
				$this->log(str_repeat(chr(8), strlen($index . ' / ' . $count)), Logger::DEBUG, [
					'writeLine' => false
				]);
				$index++;
				$this->log($index . ' / ' . $count, Logger::DEBUG, [
					'writeLine' => false
				]);
			}
			$this->log(' Done');
		}

		// delete keys
		$count = count($keysToDelete);
		if ($count != 0)
		{
			$index = 0;
			$this->log('Found ' . $count . ' keys to delete: ' . $index . ' / ' . $count, Logger::DEBUG, [
				'writeLine' => false
			]);
			foreach ($keysToDelete as $keyToDelete)
			{
				if ($this->getPhraseAppKeys()->delete($keyToDelete) === false)
				{
					throw new FailureDeleteKey($keyToDelete);
				}

				$this->removeTranslationKeyFromAllLocales($keyToDelete);

				// write to log
				$this->log(str_repeat(chr(8), strlen($index . ' / ' . $count)), Logger::DEBUG, [
					'writeLine' => false
				]);
				$index++;
				$this->log($index . ' / ' . $count, Logger::DEBUG, [
					'writeLine' => false
				]);
			}
			$this->log(' Done');
		}

		return $this;
	}

	/**
	 *
	 * @param string $key
	 * @return self
	 */
	protected function synchronizeKeysCreateKey($key)
	{
		if ($this->getPhraseAppKeys()->create($key) === false)
		{
			throw new FailureAddKey($key);
		}

		return $this;
	}

	/**
	 *
	 * @return self
	 */
	protected function synchronizeLocales()
	{
		// collect keys given
		$localesLocal = $this->getTranslationLocales();

		$this->log('Fetching locales from PhraseApp');
		// fetching the list of current translation keys in PhraseApp
		$localesRemote = $this->getPhraseAppLocales()->fetch();

		// find locales for sync
		$localesToCreateRemote = array_diff($localesLocal, $localesRemote);
		$localesToCreateLocale = array_diff($localesRemote, $localesLocal);

		// create locales remote
		$count = count($localesToCreateRemote);
		$this->log('Found ' . $count . ' locales to create remote');
		if ($count != 0)
		{
			foreach ($localesToCreateRemote as $localeToCreateRemote)
			{
				if ($this->getPhraseAppLocales()->create($localeToCreateRemote) === false)
				{
					throw new FailureAddLocale($localeToCreateRemote);
				}
			}
		}

		// create locales locale
		$count = count($localesToCreateLocale);
		$this->log('Found ' . $count . ' locales to create local');
		if ($count != 0)
		{
			// empty translations array
			$newTranslations = array_map(function ()
			{
				return '';
			}, $this->getTranslations($this->getConfig()->getLocaleDefault()));

			foreach ($localesToCreateLocale as $localeToCreateLocale)
			{
				$this->translations[$localeToCreateLocale] = $newTranslations;
			}
		}

		return $this;
	}
}
