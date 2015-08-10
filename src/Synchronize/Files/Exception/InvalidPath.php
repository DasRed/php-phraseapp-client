<?php
namespace DasRed\PhraseApp\Synchronize\Files\Exception;

use DasRed\PhraseApp\Synchronize\Files\Exception;

class InvalidPath extends Exception
{

	/**
	 *
	 * @param string $path
	 */
	public function __construct($path)
	{
		parent::__construct('The path "' . $path . '" is invalid.');
	}
}
