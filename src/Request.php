<?php
namespace DasRed\PhraseApp;

use DasRed\PhraseApp\Request\Exception as RequestException;
use DasRed\PhraseApp\Request\Exception\Json;
use DasRed\PhraseApp\Request\Exception\HttpStatus;
use Zend\Http\Client;
use Zend\Http\Request as HttpRequest;

class Request implements ConfigAwareInterface
{
	use ConfigAwareTrait;

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
	 * @var Client
	 */
	protected $client;

	/**
	 *
	 * @param Config $config
	 */
	public function __construct(Config $config)
	{
		$this->setConfig($config);
	}

	/**
	 *
	 * @return Client
	 */
	protected function getClient()
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
	protected function methodDelete($url, array $parameters = null)
	{
		return $this->request($url, self::METHOD_DELETE, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	protected function methodGet($url, array $parameters = null)
	{
		return $this->request($url, self::METHOD_GET, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	protected function methodPatch($url, array $parameters = null)
	{
		return $this->request($url, self::METHOD_PATCH, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	protected function methodPost($url, array $parameters = null)
	{
		return $this->request($url, self::METHOD_POST, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	protected function methodPut($url, array $parameters = null)
	{
		return $this->request($url, self::METHOD_PUT, $parameters);
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @throws RequestException
	 */
	protected function request($url, $method = self::METHOD_GET, array $parameters = null)
	{
		// request
		$client = $this->getClient();
		$client->reset()
			->setUri($this->getConfig()->getBaseUrl() . $url)
			->setMethod($method)
			->getRequest()
			->getHeaders()
			->addHeaderLine('Content-Type', 'application/json')
			->addHeaderLine('User-Agent', $this->getConfig()->getApplicationName())
			->addHeaderLine('Authorization', 'token ' . $this->getConfig()->getAccessToken());

		// set parameters by request
		if ($parameters !== null)
		{
			switch ($method)
			{
				case self::METHOD_POST:
					$client->setParameterPost($parameters);
					break;

				case self::METHOD_DELETE:
				case self::METHOD_PUT:
				case self::METHOD_PATCH:
					$jsonParameters = json_encode($parameters);
					$client->setRawBody($jsonParameters)->getRequest()->getHeaders()->addHeaderLine('Content-Length', strlen($jsonParameters));
					break;

				case self::METHOD_GET:
				default:
					$client->setParameterGet($parameters);
					break;
			}
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
		$result = json_decode($response->getBody(), JSON_OBJECT_AS_ARRAY);

		// result failed
		$jsonLastErrorNumber = json_last_error();
		if ($jsonLastErrorNumber !== JSON_ERROR_NONE)
		{
			throw new Json(json_last_error_msg(), $jsonLastErrorNumber);
		}

		return $result;
	}
}
