<?php
namespace DasRed\PhraseApp\Synchronize\Files;

use DasRed\PhraseApp\Synchronize;

interface HandlerInterface
{

	/**
	 *
	 * @param string $key
	 * @return string
	 */
	public function getDescriptionForKey($key);

	/**
	 *
	 * @param Synchronize $synchronize
	 * @return bool
	 */
	public function read(Synchronize $synchronize);

	/**
	 *
	 * @param array $translations
	 * @return bool
	 */
	public function write(Synchronize $synchronize);
}
