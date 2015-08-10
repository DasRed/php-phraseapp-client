<?php
namespace DasRed\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception;

class InvalidPreferDirection extends Exception
{
	/**
	 *
	 * @param string $direction
	 */
	public function __construct($direction)
	{
		parent::__construct('Unknown prefer direction "' . $direction . '" for synchronize');
	}
}