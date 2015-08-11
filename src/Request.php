<?php
namespace DasRed\PhraseApp;

use DasRed\PhraseApp\Request\Exception as RequestException;
use DasRed\PhraseApp\Request\Exception\Json;
use DasRed\PhraseApp\Request\Exception\HttpStatus;
use Zend\Http\Client;
use Zend\Http\Request as HttpRequest;

class Request
{

	/**
	 *
	 * @var string
	 */
	const METHOD_DELETE = HttpRequest::METHOD_DELETE;

	/**
	 *
	 * @var string
	 */
	const METHOD_GET = HttpRequest::METHOD_GET;

	/**
	 *
	 * @var string
	 */
	const METHOD_PATCH = HttpRequest::METHOD_PATCH;

	/**
	 *
	 * @var string
	 */
	const METHOD_POST = HttpRequest::METHOD_POST;

	/**
	 *
	 * @var string
	 */
	const METHOD_PUT = HttpRequest::METHOD_PUT;

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
	 * @var Client
	 */
	protected $client;

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
	public function getAuthToken()
	{
		return $this->authToken;
	}

	/**
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}

	/**
	 *
	 * @return Client
	 */
	public function getClient()
	{
		if ($this->client === null)
		{
			$adapter = new \Zend\Http\Client\Adapter\Curl();
			$adapter->setCurlOption(CURLOPT_RETURNTRANSFER, true)->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);

			$this->client = new Client();
			$this->client->setAdapter($adapter);
		}

		return $this->client;
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
	 * @throws RequestException
	 */
	protected function request($url, $method = self::METHOD_GET, array $parameters = array())
	{
		$url = $this->getBaseUrl() . $url;
		$parameters = array_merge([
			'auth_token' => $this->getAuthToken(),
			'project_auth_token' => $this->getAuthToken(),
		], $parameters);

		// request
		$client = $this->getClient();
		$client->reset()->setUri($url)->setMethod($method);

		// set parameters by request
		switch ($method)
		{
			case self::METHOD_POST:
				$client->setParameterPost($parameters);
				break;

			case self::METHOD_DELETE:
			case self::METHOD_PUT:
			case self::METHOD_PATCH:
				$jsonParameters = json_encode($parameters);
				$client->setRawBody($jsonParameters)
					->getRequest()
					->getHeaders()
					->addHeaderLine('Content-Type', 'application/json')
					->addHeaderLine('Content-Length', strlen($jsonParameters));
				break;

			case self::METHOD_GET:
			default:
				$client->setParameterGet($parameters);
				break;
		}

		try
		{
			$response = $client->send();
		}
		catch (\Exception $exception)
		{
			throw new RequestException($exception->getMessage(), $exception->getCode());
		}

		// react on request info / header status
		if ((int)floor($response->getStatusCode() / 100) !== 2)
		{
			throw new HttpStatus($response->getBody(), (int)$response->getStatusCode());
		}

		// convert json
		try
		{
			$result = json_decode($response->getBody(), JSON_OBJECT_AS_ARRAY);
		}
		catch (\Exception $exception)
		{
			throw new RequestException($exception->getMessage(), $exception->getCode());
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
