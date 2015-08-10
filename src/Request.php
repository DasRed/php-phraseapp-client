<?php
namespace DasRed\PhraseApp;

use DasRed\PhraseApp\Request\Exception;
use DasRed\PhraseApp\Request\Exception\Curl;
use DasRed\PhraseApp\Request\Exception\Json;
use DasRed\PhraseApp\Request\Exception\HttpStatus;

class Request
{

	const METHOD_DELETE = 'delete';

	const METHOD_GET = 'get';

	const METHOD_PATCH = 'patch';

	const METHOD_POST = 'post';

	const METHOD_PUT = 'put';

	/**
	 *
	 * @var string
	 */
	protected $authToken = null;

	/**
	 *
	 * @var string
	 */
	protected $baseUrl = null;

	/**
	 *
	 * @param string $baseUrl
	 * @param string $authToken
	 */
	public function __construct($baseUrl, $authToken)
	{
		$this->setAuthToken($authToken)->setBaseUrl($baseUrl);
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
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	protected function methodDelete($url, array $parameters = array())
	{
		return $this->request($url, self::METHOD_DELETE, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	protected function methodGet($url, array $parameters = array())
	{
		return $this->request($url, self::METHOD_GET, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	protected function methodPatch($url, array $parameters = array())
	{
		return $this->request($url, self::METHOD_PATCH, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	protected function methodPost($url, array $parameters = array())
	{
		return $this->request($url, self::METHOD_POST, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	protected function methodPut($url, array $parameters = array())
	{
		return $this->request($url, self::METHOD_PUT, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @throws Exception
	 */
	protected function request($url, $method = self::METHOD_GET, array $parameters = array())
	{
		$url = $this->getBaseUrl() . $url;
		$parameters = array_merge([
			'auth_token' => $this->getAuthToken(),
			'project_auth_token' => $this->getAuthToken()
		], $parameters);

		try
		{
			// request
			$curl = curl_init();

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			switch ($method)
			{
				case self::METHOD_POST:
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_POST, true);

					$parameterQuery = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($parameters));
					curl_setopt($curl, CURLOPT_POSTFIELDS, $parameterQuery);
					break;

				case self::METHOD_DELETE:
				case self::METHOD_PUT:
				case self::METHOD_PATCH:
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));

					$jsonParameters = json_encode($parameters);
					curl_setopt($curl, CURLOPT_HTTPHEADER, [
						'Content-Type: application/json',
						'Content-Length: ' . strlen($jsonParameters)
					]);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonParameters);
					break;

				case self::METHOD_GET:
				default:
					$parameterQuery = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($parameters));
					curl_setopt($curl, CURLOPT_URL, $url . '?' . $parameterQuery);
					break;
			}

			$responseBody = curl_exec($curl);
		}
		catch (\Exception $exception)
		{
			throw new Exception($exception->getMessage(), $exception->getCode());
		}

		// react on request info / header status
		$requestInfo = curl_getinfo($curl);
		if ((int)floor($requestInfo['http_code'] / 100) !== 2)
		{
			throw new HttpStatus($responseBody, (int)$requestInfo['http_code']);
		}

		// response failed
		if ($responseBody === false)
		{
			throw new Curl(curl_error($curl), curl_errno($curl));
		}

		// close the connection
		curl_close($curl);

		// convert json
		try
		{
			$result = json_decode($responseBody, JSON_OBJECT_AS_ARRAY);
		}
		catch (\Exception $exception)
		{
			throw new Exception($exception->getMessage(), $exception->getCode());
		}

		// result failed
		$jsonLastErrorNumber = json_last_error();
		if ($jsonLastErrorNumber !== JSON_ERROR_NONE)
		{
			throw new Json(json_last_error_msg(), $jsonLastErrorNumber);
		}

		return $result;
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
		$this->baseUrl = rtrim($baseUrl, '/') . '/';

		return $this;
	}
}
