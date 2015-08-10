<?php
namespace DasRed\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception;

class FailureAddLocale extends Exception
{

	/**
	 *
	 * @param string $localeToCreateRemote
	 */
	public function __construct($localeToCreateRemote)
	{
		parent::__construct('Can not create locale "' . $localeToCreateRemote . '".');
	}
}
