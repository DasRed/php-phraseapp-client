<?php
namespace DasRed\PhraseApp\Request;

interface LocalesAwareInterface
{

	/**
	 * @return Locales
	 */
	public function getPhraseAppLocales();
}