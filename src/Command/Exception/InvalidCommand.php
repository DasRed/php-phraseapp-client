<?php
namespace DasRed\PhraseApp\Command\Exception;

use DasRed\PhraseApp\Command\Exception;

class InvalidCommand extends Exception
{
	/**
	 *
	 * @param string $command
	 */
	public function __construct($command)
	{
		parent::__construct('Command "' . $command . '" not found.');
	}
}