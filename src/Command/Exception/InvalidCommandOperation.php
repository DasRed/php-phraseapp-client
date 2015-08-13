<?php
namespace DasRed\PhraseApp\Command\Exception;

use DasRed\PhraseApp\Command\Exception;

class InvalidCommandOperation extends Exception
{
	/**
	 *
	 * @param string $command
	 */
	public function __construct($command, $operation)
	{
		parent::__construct('Operation "' . $operation . '" not found for command "' . $command . '".');
	}
}