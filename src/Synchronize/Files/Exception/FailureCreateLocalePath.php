<?php
namespace DasRed\PhraseApp\Synchronize\Files\Exception;

use DasRed\PhraseApp\Synchronize\Files\Exception;

class FailureCreateLocalePath extends Exception
{

	/**
	 *
	 * @param string $path
	 * @param string $locale
	 */
	public function __construct($path, $locale)
	{
		parent::__construct('Locale path "' . $path . $locale . '" can not be created.');
	}
}
