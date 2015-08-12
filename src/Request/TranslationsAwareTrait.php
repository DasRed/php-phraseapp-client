<?php
namespace DasRed\PhraseApp\Request;

use DasRed\PhraseApp\Config;

trait TranslationsAwareTrait
{

	/**
	 *
	 * @var Translations
	 */
	protected $phraseAppTranslations;

	/**
	 * @return Config
	 */
	abstract public function getConfig();

	/**
	 * @return Keys
	 */
	abstract function getPhraseAppKeys();

	/**
	 * @return Locales
	 */
	abstract function getPhraseAppLocales();

	/**
	 * @return Translations
	 */
	public function getPhraseAppTranslations()
	{
		if ($this->phraseAppTranslations === null)
		{
			$this->phraseAppTranslations = new Translations($this->getConfig());
			$this->phraseAppTranslations->setPhraseAppKeys($this->getPhraseAppKeys())->setPhraseAppLocales($this->getPhraseAppLocales());
		}

		return $this->phraseAppTranslations;
	}

	/**
	 *
	 * @param Translations $translations
	 * @return self
	 */
	public function setPhraseAppTranslations(Translations $translations)
	{
		$this->phraseAppTranslations = $translations;

		return $this;
	}
}