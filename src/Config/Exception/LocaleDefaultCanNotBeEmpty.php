<?php
namespace DasRed\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception;

class LocaleDefaultCanNotBeEmpty extends Exception
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct('The default locale can not be empty.');
	}
}