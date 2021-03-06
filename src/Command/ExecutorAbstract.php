<?php
namespace DasRed\PhraseApp\Command;

use DasRed\PhraseApp\ConfigAwareInterface;
use DasRed\PhraseApp\ConfigAwareTrait;
use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\Command\Exception\InvalidArguments;
use DasRed\Zend\Console\ConsoleAwareInterface;
use DasRed\Zend\Console\ConsoleAwareTrait;
use Zend\Console\Adapter\AdapterInterface;

abstract class ExecutorAbstract implements ConfigAwareInterface, ConsoleAwareInterface
{
	use ConfigAwareTrait;
	use ConsoleAwareTrait;

	/**
	 *
	 * @var array
	 */
	protected $arguments;

	/**
	 *
	 * @param Config $config
	 * @param AdapterInterface $console
	 * @param array $arguments
	 */
	public function __construct(Config $config, AdapterInterface $console, array $arguments)
	{
		$this->setConfig($config)->setConsole($console)->setArguments($arguments);
	}

	/**
	 * @return array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * @return bool
	 */
	abstract public function execute();

	/**
	 * @param array $arguments
	 * @return self
	 */
	public function setArguments(array $arguments)
	{
		if ($this->validateArguments($arguments) === false)
		{
			throw new InvalidArguments();
		}

		$this->arguments = $arguments;

		return $this;
	}

	/**
	 *
	 * @param array $arguments
	 * @return bool
	 */
	protected function validateArguments($arguments)
	{
		foreach ($arguments as $argument)
		{
			if (empty($argument) === true)
			{
				return false;
			}
		}

		return true;
	}
}