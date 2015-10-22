<?php
namespace DasRed\PhraseApp;

use DasRed\PhraseApp\Synchronize\Exception\FailureAddKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureDeleteKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureAddLocale;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContent;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContentByTag;
use DasRed\PhraseApp\Request\KeysAwareInterface;
use DasRed\PhraseApp\Request\LocalesAwareInterface;
use DasRed\PhraseApp\Request\TranslationsAwareInterface;
use DasRed\PhraseApp\Request\KeysAwareTrait;
use DasRed\PhraseApp\Request\LocalesAwareTrait;
use DasRed\PhraseApp\Request\TranslationsAwareTrait;
use DasRed\Zend\Console\ConsoleAwareInterface;
use DasRed\Zend\Console\ConsoleAwareTrait;
use Zend\Console\Adapter\AdapterInterface;
use Zend\ProgressBar\ProgressBar;
use Zend\ProgressBar\Adapter\Console;
use Zend\Console\ColorInterface;

class Synchronize implements ConsoleAwareInterface, ConfigAwareInterface, KeysAwareInterface, LocalesAwareInterface, TranslationsAwareInterface
{
	use ConsoleAwareTrait;
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
	 * @param AdapterInterface $console
	 * @param Config $config
	 */
	public function __construct(AdapterInterface $console, Config $config)
	{
		$this->setConsole($console)->setConfig($config);
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
	 * @param int $count
	 * @return ProgressBar
	 */
	protected function getProgressBar($count)
	{
		$progressBar = new ProgressBar($this->getProgressBarAdapter(), 0, $count);
		$progressBar->update(0);

		return $progressBar;
	}

	/**
	 * @return Console
	 */
	protected function getProgressBarAdapter()
	{
		return new Console([
			'finishAction' => Console::FINISH_ACTION_CLEAR_LINE
		]);
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

		$this->getConsole()->write('Fetching full translation contents: ');
		$contentRemoteCompleteByLocale = $this->getPhraseAppTranslations()->fetch();
		$this->getConsole()->writeLine('Done', ColorInterface::LIGHT_GREEN);

		$this->getConsole()->writeLine('Updating translation contents');
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
				$keysLocalToStore = array_diff(array_keys($contentLocale), array_keys($contentRemote));
				$countDifferencesLocal += count($keysLocalToStore);
				$this->translations[$locale] = array_merge($contentLocale, $contentRemote);
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
				$this->getConsole()->write('  ' . $locale . ': ');
				$this->getConsole()->write(number_format($count, 0, ',', '.') . ' ', ColorInterface::GREEN);

				$progressBar = $this->getProgressBar($count);

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

					$progressBar->next();
				}
				$progressBar->finish();

				$this->getConsole()->writeLine('Done', ColorInterface::LIGHT_GREEN);
			}
		}

		$this->getConsole()->write('Found ');
		$this->getConsole()->write(number_format($countDifferencesLocal, 0, ',', '.'), ColorInterface::LIGHT_GREEN);
		$this->getConsole()->write(' local and ');
		$this->getConsole()->write(number_format($countDifferencesRemote, 0, ',', '.'), ColorInterface::LIGHT_GREEN);
		$this->getConsole()->writeLine(' remote differences');

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
		$this->getConsole()->write('Fetching remote keys: ');
		$keysRemote = $this->getPhraseAppKeys()->fetch();

		// find keys for sync
		$keysToCreate = array_diff($keysLocal, $keysRemote);
		$keysToDelete = array_diff($keysRemote, $keysLocal);

		// create keys
		$count = count($keysToCreate);
		$this->getConsole()->write(number_format($count, 0, ',', '.') . 'x ', ColorInterface::GREEN);
		if ($count != 0)
		{
			$progressBar = $this->getProgressBar($count);
			foreach ($keysToCreate as $keyToCreate)
			{
				$this->synchronizeKeysCreateKey($keyToCreate);
				$progressBar->next();
			}
			$progressBar->finish();
		}
		$this->getConsole()->writeLine('Done', ColorInterface::LIGHT_GREEN);

		// delete keys
		$count = count($keysToDelete);
		$this->getConsole()->write('Keys to delete: ');
		$this->getConsole()->write(number_format($count, 0, ',', '.') . 'x ', ColorInterface::GREEN);
		if ($count != 0)
		{
			$progressBar = $this->getProgressBar($count);
			foreach ($keysToDelete as $keyToDelete)
			{
				if ($this->getPhraseAppKeys()->delete($keyToDelete) === false)
				{
					throw new FailureDeleteKey($keyToDelete);
				}

				$this->removeTranslationKeyFromAllLocales($keyToDelete);

				$progressBar->next();
			}
			$progressBar->finish();
		}

		$this->getConsole()->writeLine('Done', ColorInterface::LIGHT_GREEN);

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
		$this->getConsole()->write('Snychronize remote locales: ');

		// collect keys given
		$localesLocal = $this->getTranslationLocales();

		// fetching the list of current translation keys in PhraseApp
		$localesRemote = $this->getPhraseAppLocales()->fetch();

		// find locales for sync
		$localesToCreateRemote = array_diff($localesLocal, $localesRemote);
		$localesToCreateLocale = array_diff($localesRemote, $localesLocal);

		$localeSource = null;
		if ($this->getConfig()->getUseLocaleDefaultAsLocaleSource() === true)
		{
			$localeSource = $this->getConfig()->getLocaleDefault();
		}

		// create locales remote
		$count = count($localesToCreateRemote);
		$this->getConsole()->write(number_format($count, 0, ',', '.') . 'x ', ColorInterface::GREEN);
		if ($count != 0)
		{
			$progressBar = $this->getProgressBar($count);
			foreach ($localesToCreateRemote as $localeToCreateRemote)
			{
				if ($this->getPhraseAppLocales()->create($localeToCreateRemote, $localeSource) === false)
				{
					throw new FailureAddLocale($localeToCreateRemote);
				}

				$progressBar->next();
			}
			$progressBar->finish();
		}
		$this->getConsole()->writeLine('Done', ColorInterface::LIGHT_GREEN);

		// create locales locale
		$count = count($localesToCreateLocale);
		$this->getConsole()->write('Snychronize local locales: ');
		$this->getConsole()->write(number_format($count, 0, ',', '.') . 'x ', ColorInterface::GREEN);
		if ($count != 0)
		{
			$progressBar = $this->getProgressBar($count);
			// empty translations array
			foreach ($localesToCreateLocale as $localeToCreateLocale)
			{
				$this->translations[$localeToCreateLocale] = [];
				$progressBar->next();
			}
			$progressBar->finish();
		}

		$this->getConsole()->writeLine('Done', ColorInterface::LIGHT_GREEN);

		return $this;
	}
}
