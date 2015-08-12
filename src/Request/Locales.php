<?php
namespace DasRed\PhraseApp\Request;

use DasRed\PhraseApp\Request;
use DasRed\PhraseApp\Exception as BaseException;

/**
 *
 * @see http://docs.phraseapp.com/api/v2/locales/
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
		try
		{
			$response = $this->methodGet(self::URL_API);
		}
		catch (BaseException $exception)
		{
			return [];
		}

		return array_map(function ($entry)
		{
			return $entry['code'];
		}, $response);
	}
}
