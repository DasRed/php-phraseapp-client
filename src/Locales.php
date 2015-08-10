<?php
namespace DasRed\PhraseApp;

/**
 *
 * @see https://phraseapp.com/docs/api/locales?language=en
 */
class Locales extends Request
{

	const URL_API = 'locales/';

	/**
	 *
	 * @param string $locale
	 * @return boolean
	 */
	public function create($locale)
	{
		try
		{
			$this->methodPost(self::URL_API, [
				'locale' => [
					'name' => $locale
				]
			]);
		}
		catch (Exception $exception)
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
		try
		{
			$response = $this->methodGet(self::URL_API);
		}
		catch (Exception $exception)
		{
			return [];
		}

		return array_map(function ($entry)
		{
			return $entry['code'];
		}, $response);
	}
}
