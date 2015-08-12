<?php
namespace DasRed\PhraseApp;

use DasRed\PhraseApp\Config\Exception\InvalidPreferDirection;

class Config
{

	/**
	 *
	 * @var string
	 */
	const PREFER_REMOTE = 'remote';

	/**
	 *
	 * @var string
	 */
	const PREFER_LOCAL = 'local';

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
	 * @var string
	 */
	protected $localeDefault;

	/**
	 *
	 * @var string
	 */
	protected $preferDirection = self::PREFER_REMOTE;

	/**
	 * @var string
	 */
	protected $projectId;

	/**
	 *
	 * @var string
	 */
	protected $tagForContentChangeFromLocalToRemote;

	/**
	 *
	 * @param string $projectId
	 * @param string $accessToken
	 * @param string $localeDefault
	 * @param string $applicationName
	 * @param string $baseUrl
	 */
	public function __construct($projectId, $accessToken, $localeDefault, $applicationName = 'PHP PhraseApp Client (https://github.com/DasRed/php-phraseapp-client)', $baseUrl = 'https://api.phraseapp.com/api/v2/')
	{
		$this->setProjectId($projectId)->setAccessToken($accessToken)->setLocaleDefault($localeDefault)->setApplicationName($applicationName)->setBaseUrl($baseUrl);
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
	 * @return string
	 */
	public function getLocaleDefault()
	{
		return $this->localeDefault;
	}

	/**
	 *
	 * @return string
	 */
	public function getPreferDirection()
	{
		return $this->preferDirection;
	}

	/**
	 * @return string
	 */
	public function getProjectId()
	{
		return $this->projectId;
	}

	/**
	 *
	 * @return string
	 */
	public function getTagForContentChangeFromLocalToRemote()
	{
		return $this->tagForContentChangeFromLocalToRemote;
	}

	/**
	 *
	 * @param string $accessToken
	 * @return self
	 */
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;

		return $this;
	}

	/**
	 *
	 * @param string $applicationName
	 * @return self
	 */
	public function setApplicationName($applicationName)
	{
		$this->applicationName = $applicationName;

		return $this;
	}

	/**
	 *
	 * @param string $baseUrl
	 * @return self
	 */
	public function setBaseUrl($baseUrl)
	{
		$this->baseUrl = rtrim($baseUrl, '/') . '/';

		return $this;
	}

	/**
	 * @param string $localeDefault
	 * @return self
	 */
	public function setLocaleDefault($localeDefault)
	{
		$this->localeDefault = $localeDefault;

		return $this;
	}

	/**
	 *
	 * @param string $preferDirection
	 * @return self
	 */
	public function setPreferDirection($preferDirection)
	{
		if (in_array($preferDirection, [
			self::PREFER_LOCAL,
			self::PREFER_REMOTE
		]) === false)
		{
			throw new InvalidPreferDirection($preferDirection);
		}

		$this->preferDirection = $preferDirection;

		return $this;
	}

	/**
	 * @param string $projectId
	 * @return self
	 */
	public function setProjectId($projectId)
	{
		$this->projectId = $projectId;

		return $this;
	}

	/**
	 *
	 * @param string $tagForContentChangeFromLocalToRemote
	 * @return self
	 */
	public function setTagForContentChangeFromLocalToRemote($tagForContentChangeFromLocalToRemote)
	{
		$this->tagForContentChangeFromLocalToRemote = $tagForContentChangeFromLocalToRemote;

		return $this;
	}

}