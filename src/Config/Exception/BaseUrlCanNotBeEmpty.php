<?php
namespace DasRed\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception;

class BaseUrlCanNotBeEmpty extends Exception
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct('The base url can not be empty.');
	}
}