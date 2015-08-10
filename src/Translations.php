<?php
namespace DasRed\PhraseApp;

/**
 *
 * @see https://phraseapp.com/docs/api/translations?language=en#index
 */
class Translations extends Request
{

	const URL_API = 'translations/';

	/**
	 *
	 * @param string $locale
	 * @return array
	 */
	public function fetch($locale = null)
	{
		if ($locale === null)
		{
			return $this->fetchAll();
		}

		return $this->fetchForLocale($locale);
	}

	/**
	 *
	 * @return array
	 */
	protected function fetchAll()
	{
		try
		{
			$response = $this->methodGet(self::URL_API);
		}
		catch (Exception $exception)
		{
			return [];
		}

		$result = [];
		foreach ($response as $locale => $responseLocaleEntry)
		{
			$result = array_merge($result, [
				$locale => $this->parse($responseLocaleEntry)
			]);
		}

		return $result;
	}

	/**
	 *
	 * @param string $locale
	 * @return array
	 */
	protected function fetchForLocale($locale)
	{
		try
		{
			$response = $this->methodGet(self::URL_API, [
				'locale_name' => $locale
			]);
		}
		catch (Exception $exception)
		{
			return [];
		}

		return $this->parse($response);
	}

	/**
	 *
	 * @param array $entries
	 * @return array
	 */
	protected function parse(array $entries = array())
	{
		$result = [];

		foreach ($entries as $entry)
		{
			$key = $entry['translation_key']['name'];
			$content = $entry['content'];

			$result[$key] = $content;
		}

		return $result;
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
		try
		{
			$this->methodPost(self::URL_API . 'store', [
				'locale' => $locale,
				'key' => $key,
				'content' => $content
			]);
		}
		catch (Exception $exception)
		{
			return false;
		}

		return true;
	}
}
