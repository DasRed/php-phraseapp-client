<?php
namespace DasRed\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception;

class AccessTokenCanNotBeEmpty extends Exception
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct('The access token can not be empty.');
	}
}