<?php
namespace DasRed\PhraseApp\Request;

interface TranslationsAwareInterface
{

	/**
	 * @return Translations
	 */
	public function getPhraseAppTranslations();

	/**
	 *
	 * @param Translations $translations
	 * @return self
	 */
	public function setPhraseAppTranslations(Translations $translations);
}