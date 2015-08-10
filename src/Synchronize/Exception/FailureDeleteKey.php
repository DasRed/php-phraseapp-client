<?php
namespace DasRed\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception;

class FailureDeleteKey extends Exception
{

	/**
	 *
	 * @param string $keyToDelete
	 */
	public function __construct($keyToDelete)
	{
		parent::__construct('Can not delete translation key "' . $keyToDelete . '".');
	}
}
