<?php
namespace DasRed\PhraseApp\Request;

use DasRed\PhraseApp\Request;
use DasRed\PhraseApp\Exception as BaseException;
use DasRed\PhraseApp\Collection\SubArrayAwareTrait;

/**
 *
 * @see http://docs.phraseapp.com/api/v2/translations/
 */
class Translations extends Request implements KeysAwareInterface, LocalesAwareInterface
{
	use KeysAwareTrait;
	use LocalesAwareTrait;
	use SubArrayAwareTrait;
	const URL_API = 'projects/:project_id/translations/';

	/**
	 *
	 * @param string $localeId
	 * @param string $keyId
	 * @param string $content
	 * @return bool
	 */
	protected function create($localeId, $keyId, $content)
	{
		try
		{
			$response = $this->methodPost(self::URL_API, [
				'locale_id' => $localeId,
				'key_id' => $keyId,
				'content' => $content
			]);

			$this->getCollection()->append($response);
		}
		catch (BaseException $exception)
		{
			return false;
		}

		return true;
	}

	/**
	 *
	 * @return array
	 */
	public function fetch()
	{
		$result = [];
		foreach ($this->getCollection() as $entry)
		{
			$locale = $entry['locale']['code'];
			$key = $entry['key']['name'];
			$content = $entry['content'];

			if (array_key_exists($locale, $result) === false)
			{
				$result[$locale] = [];
			}

			$result[$locale][$key] = $content;
		}

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getIdKey()
	{
		return 'id';
	}

	/**
	 * @return array
	 * @see http://docs.phraseapp.com/api/v2/translations/#index
	 */
	protected function load()
	{
		try
		{
			$response = $this->methodGet(self::URL_API);
		}
		catch (BaseException $exception)
		{
			$response = [];
		}

		return $response;
	}

	/**
	 *
	 * @param string $locale
	 * @param string $key
	 * @param string $content
	 * @return boolean
	 */
	public function store($locale, $key, $content)
	{
		// find locale
		$locale = $this->getPhraseAppLocales()->getCollection()->get($locale);
		if ($locale === null)
		{
			return false;
		}

		// find key
		$key = $this->getPhraseAppKeys()->getCollection()->get($key);
		if ($key === null)
		{
			return false;
		}

		// find the translation
		$translation = $this->getCollection()->find(function (array $entry) use($locale, $key)
		{
			return $entry['locale']['id'] == $locale['id'] && $entry['key']['id'] == $key['id'];
		});

		// not found, create the content
		if ($translation === null)
		{
			return $this->create($locale['id'], $key['id'], $content);
		}

		return $this->update($translation['id'], $content);
	}

	/**
	 *
	 * @param string $translationId
	 * @param string $content
	 * @return bool
	 */
	protected function update($translationId, $content)
	{
		try
		{
			$response = $this->methodPatch(self::URL_API . $translationId, [
				'content' => $content
			]);

			$this->getCollection()->append($response);
		}
		catch (BaseException $exception)
		{
			return false;
		}

		return true;
	}
}
