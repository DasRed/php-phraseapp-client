<?php
namespace DasRed\PhraseApp\Request;

interface KeysAwareInterface
{

	/**
	 * @return Keys
	 */
	public function getPhraseAppKeys();

	/**
	 *
	 * @param Keys $keys
	 * @return self
	 */
	public function setPhraseAppKeys(Keys $keys);
}