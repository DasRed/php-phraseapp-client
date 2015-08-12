<?php
namespace DasRed\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception;

class InvalidPreferDirection extends Exception
{
	/**
	 *
	 * @param string $direction
	 */
	public function __construct($direction)
	{
		parent::__construct('Unknown prefer direction "' . $direction . '".');
	}
}