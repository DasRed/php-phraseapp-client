<?php
namespace DasRed\PhraseApp;

interface ConfigAwareInterface
{

	/**
	 * @return Config
	 */
	public function getConfig();

	/**
	 *
	 * @param Config $config
	 * @return self
	 */
	public function setConfig(Config $config);
}
