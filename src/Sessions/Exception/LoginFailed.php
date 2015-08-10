<?php
namespace DasRed\PhraseApp\Sessions\Exception;

use DasRed\PhraseApp\Sessions\Exception;

class LoginFailed extends Exception
{

	/**
	 *
	 * @param string $email
	 */
	public function __construct($email)
	{
		parent::__construct('Can not login with credentials "' . $email . '".');
	}
}