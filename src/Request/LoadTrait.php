<?php
namespace DasRed\PhraseApp\Request;

use DasRed\PhraseApp\Exception as BaseException;

trait LoadTrait
{

	/**
	 * @return int
	 */
	protected function getMaxPerPage()
	{
		return 100;
	}

	/**
	 * @return string
	 */
	abstract protected function getUrlApi();

	/**
	 * @param int $page
	 * @param int $perPage
	 * @return array
	 */
	protected function load()
	{
		$response = [];
		$page = 1;
		$perPage = $this->getMaxPerPage();

		try
		{
			do
			{
				$result = $this->methodGet($this->getUrlApi(), [
					'page' => $page,
					'per_page' => $perPage
				]);
				$response = array_merge($response, $result);

				$page++;
			}
			while (count($result) >= $perPage);
		}
		catch (BaseException $exception)
		{
			// nothing to do
		}

		return $response;
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	abstract protected function methodGet($url, array $parameters = null);
}