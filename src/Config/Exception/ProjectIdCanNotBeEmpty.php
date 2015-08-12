<?php
namespace DasRed\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception;

class ProjectIdCanNotBeEmpty extends Exception
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct('The project id can not be empty.');
	}
}