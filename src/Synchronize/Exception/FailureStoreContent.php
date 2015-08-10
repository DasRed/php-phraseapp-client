<?php
namespace DasRed\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception;

class FailureStoreContent extends Exception
{

	/**
	 *
	 * @param string $keyLocalToStore
	 */
	public function __construct($keyLocalToStore)
	{
		parent::__construct('Can not store translation content for translation key "' . $keyLocalToStore . '".');
	}
}