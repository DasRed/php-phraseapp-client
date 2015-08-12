<?php
namespace DasRed\PhraseApp\Request;

use DasRed\PhraseApp\Config;

trait KeysAwareTrait
{
	/**
	 *
	 * @var Keys
	 */
	protected $phraseAppKeys;

	/**
	 * @return Config
	 */
	abstract public function getConfig();

	/**
	 * @return Keys
	 */
	public function getPhraseAppKeys()
	{
		if ($this->phraseAppKeys === null)
		{
			$this->phraseAppKeys = new Keys($this->getConfig());
		}

		return $this->phraseAppKeys;
	}
}