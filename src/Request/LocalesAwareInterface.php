<?php
namespace DasRed\PhraseApp\Request;

interface LocalesAwareInterface
{

	/**
	 * @return Locales
	 */
	public function getPhraseAppLocales();

	/**
	 *
	 * @param Locales $locales
	 * @return self
	 */
	public function setPhraseAppLocales(Locales $locales);
}