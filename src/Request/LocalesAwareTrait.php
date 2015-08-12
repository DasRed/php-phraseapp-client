<?php
namespace DasRed\PhraseApp\Request;

use DasRed\PhraseApp\Config;

trait LocalesAwareTrait
{
	/**
	 *
	 * @var Locales
	 */
	protected $phraseAppLocales;

	/**
	 * @return Config
	 */
	abstract public function getConfig();

	/**
	 * @return Locales
	 */
	public function getPhraseAppLocales()
	{
		if ($this->phraseAppLocales === null)
		{
			$this->phraseAppLocales = new Locales($this->getConfig());
		}

		return $this->phraseAppLocales;
	}

	/**
	 *
	 * @param Locales $locales
	 * @return self
	 */
	public function setPhraseAppLocales(Locales $locales)
	{
		$this->phraseAppLocales = $locales;

		return $this;
	}
}