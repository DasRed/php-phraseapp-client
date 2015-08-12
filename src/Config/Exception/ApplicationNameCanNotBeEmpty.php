<?php
namespace DasRed\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception;

class ApplicationNameCanNotBeEmpty extends Exception
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct('The application name can not be empty.');
	}
}