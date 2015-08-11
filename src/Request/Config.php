<?php
namespace DasRed\PhraseApp\Request;

class Config
{

	/**
	 *
	 * @var string
	 */
	protected $accessToken;

	/**
	 *
	 * @var string
	 */
	protected $applicationName;

	/**
	 *
	 * @var string
	 */
	protected $baseUrl;

	/**
	 *
	 * @param string $accessToken
	 * @param string $applicationName
	 * @param string $baseUrl
	 */
	public function __construct($accessToken, $applicationName, $baseUrl)
	{
		$this->setAccessToken($accessToken)->setApplicationName($applicationName)->setBaseUrl($baseUrl);
	}

	/**
	 *
	 * @return string
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}

	/**
	 *
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->applicationName;
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
	 * @param string $accessToken
	 * @return self
	 */
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
	}

	/**
	 *
	 * @param string $applicationName
	 * @return self
	 */
	public function setApplicationName($applicationName)
	{
		$this->applicationName = $applicationName;
	}

	/**
	 *
	 * @param string $baseUrl
	 * @return self
	 */
	public function setBaseUrl($baseUrl)
	{
		$this->baseUrl = $baseUrl;
	}
}