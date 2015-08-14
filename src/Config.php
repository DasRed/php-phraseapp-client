<?php
namespace DasRed\PhraseApp;

use DasRed\PhraseApp\Config\Exception\InvalidPreferDirection;
use DasRed\PhraseApp\Config\Exception\ProjectIdCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception\AccessTokenCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception\LocaleDefaultCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception\ApplicationNameCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception\BaseUrlCanNotBeEmpty;

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
	protected $applicationName = 'PHP PhraseApp Client (https://github.com/DasRed/php-phraseapp-client)';

	/**
	 *
	 * @var string
	 */
	protected $baseUrl = 'https://api.phraseapp.com/api/v2/';

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
	protected $tagForContentChangeFromLocalToRemote = 'newContent';

	/**
	 * @var bool
	 */
	protected $useLocaleDefaultAsLocaleSource = true;

	/**
	 *
	 * @param string $projectId
	 * @param string $accessToken
	 * @param string $localeDefault
	 */
	public function __construct($projectId, $accessToken, $localeDefault)
	{
		$this->setProjectId($projectId)->setAccessToken($accessToken)->setLocaleDefault($localeDefault);
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
	 * @return bool
	 */
	public function getUseLocaleDefaultAsLocaleSource()
	{
		return $this->useLocaleDefaultAsLocaleSource;
	}

	/**
	 *
	 * @param string $accessToken
	 * @return self
	 */
	public function setAccessToken($accessToken)
	{
		if (empty($accessToken) === true)
		{
			throw new AccessTokenCanNotBeEmpty();
		}

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
		if (empty($applicationName) === true)
		{
			throw new ApplicationNameCanNotBeEmpty();
		}

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
		if (empty($baseUrl) === true)
		{
			throw new BaseUrlCanNotBeEmpty();
		}

		$this->baseUrl = rtrim($baseUrl, '/') . '/';

		return $this;
	}

	/**
	 * @param string $localeDefault
	 * @return self
	 */
	public function setLocaleDefault($localeDefault)
	{
		if (empty($localeDefault) === true)
		{
			throw new LocaleDefaultCanNotBeEmpty();
		}

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
		if (empty($projectId) === true)
		{
			throw new ProjectIdCanNotBeEmpty();
		}

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

	/**
	 * @param bool $useLocaleDefaultAsLocaleSource
	 * @return self
	 */
	public function setUseLocaleDefaultAsLocaleSource($useLocaleDefaultAsLocaleSource)
	{
		$this->useLocaleDefaultAsLocaleSource = (bool)$useLocaleDefaultAsLocaleSource;

		return $this;
	}
}
