<?php
namespace DasRed\PhraseApp;

use DasRed\PhraseApp\Synchronize\Exception\FailureAddKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureDeleteKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureAddLocale;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContent;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContentByTag;
use DasRed\PhraseApp\Synchronize\Exception\InvalidPreferDirection;
use DasRed\Zend\Log\LoggerAwareTrait;
use Zend\Log\Logger;
use Zend\Log\LoggerAwareInterface;

class Synchronize implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 *
	 * @var string
	 */
	const PREFER_REMOTE = 'remote';

	/**
	 *
	 * @var string
	 */
	const PREFER_LOCAL = 'local';

	/**
	 *
	 * @var string
	 */
	protected $authToken;

	/**
	 *
	 * @var string
	 */
	protected $baseUrl;

	/**
	 *
	 * @var string
	 */
	protected $localeDefault;

	/**
	 *
	 * @var Locales
	 */
	protected $phraseLocales;

	/**
	 *
	 * @var Translations
	 */
	protected $phraseTranslations;

	/**
	 *
	 * @var TranslationKeys
	 */
	protected $phraseTranslationKeys;

	/**
	 *
	 * @var string
	 */
	protected $preferDirection = self::PREFER_REMOTE;

	/**
	 *
	 * @var string
	 */
	protected $tagForContentChangeFromLocalToRemote;

	/**
	 * current translations with [LOCALE => [KEY => CONTENT]]
	 *
	 * @var array
	 */
	protected $translations = [];

	/**
	 *
	 * @var string
	 */
	protected $userEmail;

	/**
	 *
	 * @var string
	 */
	protected $userPassword;

	/**
	 *
	 * @param Logger $logger
	 * @param string $baseUrl
	 * @param string $authToken
	 * @param string $userEmail
	 * @param string $userPassword
	 * @param string $localeDefault
	 */
	public function __construct(Logger $logger, $baseUrl, $authToken, $userEmail, $userPassword, $localeDefault)
	{
		$this->setLogger($logger)
			->setLocaleDefault($localeDefault)
			->setBaseUrl($baseUrl)
			->setAuthToken($authToken)
			->setUserEmail($userEmail)
			->setUserPassword($userPassword);
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
	 * @return string
	 */
	protected function getAuthToken()
	{
		return $this->authToken;
	}

	/**
	 *
	 * @return string
	 */
	protected function getBaseUrl()
	{
		return $this->baseUrl;
	}

	/**
	 *
	 * @return string
	 */
	protected function getLocaleDefault()
	{
		return $this->localeDefault;
	}

	/**
	 *
	 * @return \PhraseApp\Locales
	 */
	protected function getPhraseLocales()
	{
		if ($this->phraseLocales === null)
		{
			$this->phraseLocales = new Locales($this->getBaseUrl(), $this->getAuthToken());
		}

		return $this->phraseLocales;
	}

	/**
	 *
	 * @return \PhraseApp\Translations
	 */
	protected function getPhraseTranslations()
	{
		if ($this->phraseTranslations === null)
		{
			$this->phraseTranslations = new Translations($this->getBaseUrl(), $this->getAuthToken());
		}

		return $this->phraseTranslations;
	}

	/**
	 *
	 * @return \PhraseApp\TranslationKeys
	 */
	protected function getPhraseTranslationKeys()
	{
		if ($this->phraseTranslationKeys === null)
		{
			$this->phraseTranslationKeys = new TranslationKeys($this->getBaseUrl(), $this->getAuthToken(), $this->getUserEmail(), $this->getUserPassword());
		}

		return $this->phraseTranslationKeys;
	}

	/**
	 *
	 * @return string
	 */
	public function getPreferDirection()
	{
		return $this->preferDirection;
	}

	/**
	 *
	 * @return string
	 */
	public function getTagForContentChangeFromLocalToRemote()
	{
		return $this->tagForContentChangeFromLocalToRemote;
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
			if ($localeA === $this->getLocaleDefault())
			{
				return - 1;
			}
			if ($localeB === $this->getLocaleDefault())
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
	protected function getTranslationKeys($locale = null)
	{
		if ($locale === null)
		{
			$result = [];
			foreach ($this->getTranslationLocales() as $locale)
			{
				$result = array_merge($result, $this->getTranslationKeys($locale));
			}

			return $result;
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
	 * @return string
	 */
	protected function getUserEmail()
	{
		return $this->userEmail;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUserPassword()
	{
		return $this->userPassword;
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
	 * @param string $authToken
	 * @return self
	 */
	protected function setAuthToken($authToken)
	{
		$this->authToken = $authToken;

		return $this;
	}

	/**
	 *
	 * @param string $baseUrl
	 * @return self
	 */
	protected function setBaseUrl($baseUrl)
	{
		$this->baseUrl = $baseUrl;

		return $this;
	}

	/**
	 *
	 * @param string $localeDefault
	 * @return self
	 */
	protected function setLocaleDefault($localeDefault)
	{
		$this->localeDefault = $localeDefault;

		return $this;
	}

	/**
	 *
	 * @param string $preferDirection
	 * @return self
	 */
	public function setPreferDirection($preferDirection)
	{
		if (in_array($preferDirection, [
			self::PREFER_LOCAL,
			self::PREFER_REMOTE
		]) === false)
		{
			throw new InvalidPreferDirection($preferDirection);
		}

		$this->preferDirection = $preferDirection;

		return $this;
	}

	/**
	 *
	 * @param string $tagForContentChangeFromLocalToRemote
	 * @return self
	 */
	public function setTagForContentChangeFromLocalToRemote($tagForContentChangeFromLocalToRemote)
	{
		$this->tagForContentChangeFromLocalToRemote = $tagForContentChangeFromLocalToRemote;

		return $this;
	}

	/**
	 *
	 * @param string $userEmail
	 * @return self
	 */
	protected function setUserEmail($userEmail)
	{
		$this->userEmail = $userEmail;

		return $this;
	}

	/**
	 *
	 * @param string $userPassword
	 * @return self
	 */
	protected function setUserPassword($userPassword)
	{
		$this->userPassword = $userPassword;

		return $this;
	}

	/**
	 *
	 * @return boolean
	 */
	public function synchronize()
	{
		$this->log('Synchronizing translations');

		// sync translation content
		try
		{
// 			$this->synchronizeLocales()->synchronizeKeys()->synchronizeContent();
			$this->synchronizeKeys();
		}
		catch (Exception $exception)
		{
			$this->log('FAILURE: ' . $exception->getMessage(), Logger::ERR);
			return false;
		}

		return true;
	}

	/**
	 *
	 * @return \PhraseApp\Synchronize
	 */
	protected function synchronizeContent()
	{
		$locales = $this->getTranslationLocales();
		$countPerLocale = count($this->getTranslationKeys($this->getLocaleDefault()));
		$count = count($locales) * $countPerLocale;

		$this->log('Fetching full translation contents');
		$contentRemoteCompleteByLocale = $this->getPhraseTranslations()->fetch();

		$this->log('Updating translation contents');
		$countDifferencesLocal = 0;
		$countDifferencesRemote = 0;

		foreach ($locales as $locale)
		{
			$contentLocale = $this->getTranslations($locale);
			$contentRemote = array_key_exists($locale, $contentRemoteCompleteByLocale) ? $contentRemoteCompleteByLocale[$locale] : [];
			$keysLocalToStore = [];

			// prefer remote
			if ($this->getPreferDirection() === self::PREFER_REMOTE)
			{
				// overwrite all from remote and send only new content from local to remote
				$countDifferencesLocal += count(array_diff($contentRemote, $contentLocale));
				$this->translations[$locale] = array_merge($contentLocale, $contentRemote);
				$keysLocalToStore = array_diff(array_keys($contentLocale), array_keys($contentRemote));
			}
			// prefer local content... no changes on local content!
			elseif ($this->getPreferDirection() === self::PREFER_LOCAL)
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
				foreach ($keysLocalToStore as $keyLocalToStore)
				{
					// add the "new" tag to key if new in defaultlocale
					if ($locale === $this->getLocaleDefault() && $this->getPhraseTranslationKeys()->addTag($keyLocalToStore, $this->getTagForContentChangeFromLocalToRemote()) === false)
					{
						throw new FailureStoreContentByTag($keyLocalToStore);
					}

					// store content remote
					$content = $contentLocale[$keyLocalToStore];
					if ($this->getPhraseTranslations()->store($locale, $keyLocalToStore, $content) === false)
					{
						throw new FailureStoreContent($keyLocalToStore);
					}
				}
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
		$keysLocal = array_keys($this->getTranslations($this->getLocaleDefault()));

		// fetching the list of current translation keys in PhraseApp
		$this->log('Fetching keys from PhraseApp');
		$keysRemote = $this->getPhraseTranslationKeys()->fetch();

		// find keys for sync
		$keysToCreate = array_diff($keysLocal, $keysRemote);
		$keysToDelete = array_diff($keysRemote, $keysLocal);

		// create keys
		$count = count($keysToCreate);
		$this->log('Found ' . $count . ' keys to create');
		if ($count != 0)
		{
			foreach ($keysToCreate as $keyToCreate)
			{
				if ($this->synchronizeKeysCreateKey($keyToCreate) === false)
				{
					throw new FailureAddKey($keyToCreate);
				}
			}
		}
// FIXME
return $this;

		// delete keys
		$count = count($keysToDelete);
		$this->log('Found ' . $count . ' keys to delete');
		if ($count != 0)
		{
			foreach ($keysToDelete as $keyToDelete)
			{
				if ($this->synchronizeKeysDeleteKey($keyToDelete) === false)
				{
					throw new FailureDeleteKey($keyToDelete);
				}

				$this->removeTranslationKeyFromAllLocales($keyToDelete);
			}
		}

		// clean up the keys in all other locales other then the main locale
		$translationsForMainLocale = $this->getTranslations($this->getLocaleDefault());
		foreach ($this->getTranslationLocales() as $locale)
		{
			if ($locale === $this->getLocaleDefault())
			{
				continue;
			}

			foreach ($this->getTranslationKeys($locale) as $keyToDelete)
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
	 * @param string $key
	 * @return boolean
	 */
	protected function synchronizeKeysCreateKey($key)
	{
		return $this->getPhraseTranslationKeys()->create($key);
	}

	/**
	 *
	 * @param string $key
	 * @return boolean
	 */
	protected function synchronizeKeysDeleteKey($key)
	{
		return $this->getPhraseTranslationKeys()->delete($key);
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
		$localesRemote = $this->getPhraseLocales()->fetch();

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
				if ($this->getPhraseLocales()->create($localeToCreateRemote) === false)
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
			}, $this->getTranslations($this->getLocaleDefault()));

			foreach ($localesToCreateLocale as $localeToCreateLocale)
			{
				$this->translations[$localeToCreateLocale] = $newTranslations;
			}
		}

		return $this;
	}
}
