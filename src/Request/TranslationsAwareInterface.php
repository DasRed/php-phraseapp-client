<?php
namespace DasRed\PhraseApp\Request;

interface TranslationsAwareInterface
{

	/**
	 * @return Translations
	 */
	public function getPhraseAppTranslations();
}