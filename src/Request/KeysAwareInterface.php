<?php
namespace DasRed\PhraseApp\Request;

interface KeysAwareInterface
{

	/**
	 * @return Keys
	 */
	public function getPhraseAppKeys();
}