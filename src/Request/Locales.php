<?php
namespace DasRed\PhraseApp\Request;

use DasRed\PhraseApp\Request;
use DasRed\PhraseApp\Exception as BaseException;
use DasRed\PhraseApp\Collection\SubArrayAwareTrait;

/**
 *
 * @see http://docs.phraseapp.com/api/v2/locales/
 */
class Locales extends Request
{
	use SubArrayAwareTrait;
	use LoadTrait;
	const URL_API = 'projects/:project_id/locales/';

	/**
	 *
	 * @param string $locale
	 * @return boolean
	 * @see http://docs.phraseapp.com/api/v2/locales/#create
	 */
	public function create($locale, $localeSource = null)
	{
		try
		{
			$parameters = [
				'name' => $locale,
				'code' => $locale
			];

			if ($localeSource !== null)
			{
				$parameters['source_locale_id'] = $localeSource;
				$localeSource = $this->getCollection()->get($localeSource);
				if ($localeSource !== null)
				{
					$parameters['source_locale_id'] = $localeSource['id'];
				}
			}

			$result = $this->methodPost(self::URL_API, $parameters);

			$this->getCollection()->append($result);
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
		return array_values($this->getCollection()->map(function ($entry)
		{
			return $entry['code'];
		}));
	}

	/**
	 * @return string
	 */
	protected function getIdKey()
	{
		return 'code';
	}

	/**
	 * @return string
	 */
	protected function getUrlApi()
	{
		return self::URL_API;
	}
}
