<?php
namespace DasRed\PhraseApp;

trait ConfigAwareTrait
{
	/**
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 *
	 * @param Config $config
	 * @return self
	 */
	public function setConfig(Config $config)
	{
		$this->config = $config;

		return $this;
	}
}
