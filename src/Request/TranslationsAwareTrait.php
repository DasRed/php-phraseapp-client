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
	 * @return Translations
	 */
	public function getPhraseAppTranslations()
	{
		if ($this->phraseAppTranslations === null)
		{
			$this->phraseAppTranslations = new Translations($this->getConfig());
		}

		return $this->phraseAppTranslations;
	}
}