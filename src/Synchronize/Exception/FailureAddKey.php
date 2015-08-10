<?php
namespace DasRed\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception;

class FailureAddKey extends Exception
{

	/**
	 *
	 * @param string $keyToCreate
	 */
	public function __construct($keyToCreate)
	{
		parent::__construct('Can not create translation key "' . $keyToCreate . '".');
	}
}
