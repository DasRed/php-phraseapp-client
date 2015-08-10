<?php
namespace DasRed\PhraseApp;

use DasRed\PhraseApp\Sessions\Exception;
use DasRed\PhraseApp\Sessions\Exception\LoginFailed;

/**
 *
 * @see https://phraseapp.com/docs/api/authentication?language=en
 */
class Sessions extends Request
{

	const URL_API = 'sessions/';

	/**
	 *
	 * @var bool
	 */
	protected $autoSession = false;

	/**
	 *
	 * @var string
	 */
	protected $sessionToken = null;

	/**
	 *
	 * @var string
	 */
	protected $userEmail = null;

	/**
	 *
	 * @var string
	 */
	protected $userPassword = null;

	/**
	 *
	 * @param string $baseUrl
	 * @param string $authToken
	 * @param string $userEmail
	 * @param string $userPassword
	 */
	public function __construct($baseUrl, $authToken, $userEmail, $userPassword)
	{
		parent::__construct($baseUrl, $authToken);

		$this->setUserEmail($userEmail)->setUserPassword($userPassword);
	}

	/**
	 * destructor
	 */
	public function __destruct()
	{
		$this->logout();
	}

	/**
	 *
	 * @return string
	 * @throws LoginFailed
	 */
	protected function getSessionToken()
	{
		if ($this->sessionToken === null && $this->login() === false)
		{
			throw new LoginFailed($this->getUserEmail());
		}

		return $this->sessionToken;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUserEmail()
	{
		return $this->userEmail;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUserPassword()
	{
		return $this->userPassword;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function login()
	{
		if ($this->sessionToken === null)
		{
			try
			{
				$result = parent::request(self::URL_API, Request::METHOD_POST, [
					'email' => $this->getUserEmail(),
					'password' => $this->getUserPassword()
				]);
			}
			catch (Exception $exception)
			{
				return false;
			}

			if ($result['success'] !== true)
			{
				return false;
			}

			$this->sessionToken = $result['auth_token'];
		}

		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function logout()
	{
		if ($this->sessionToken !== null)
		{
			try
			{
				$result = parent::request(self::URL_API, Request::METHOD_DELETE, [
					'auth_token' => $this->sessionToken
				]);
			}
			catch (Exception $exception)
			{
				return false;
			}

			if ($result['success'] !== true)
			{
				return false;
			}

			$this->sessionToken = null;
		}

		return true;
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 */
	protected function request($url, $method = self::METHOD_GET, array $parameters = array())
	{
		if ($this->autoSession === true)
		{
			$parameters = array_merge([
				'auth_token' => $this->getSessionToken()
			], $parameters);
		}

		$parameters = array_merge([
			'project_auth_token' => $this->getAuthToken()
		], $parameters);

		return parent::request($url, $method, $parameters);
	}

	/**
	 *
	 * @param string $userEmail
	 * @return self
	 */
	protected function setUserEmail($userEmail)
	{
		$this->userEmail = $userEmail;

		return $this;
	}

	/**
	 *
	 * @param string $userPassword
	 * @return self
	 */
	protected function setUserPassword($userPassword)
	{
		$this->userPassword = $userPassword;

		return $this;
	}
}
