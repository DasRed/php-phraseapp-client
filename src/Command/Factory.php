<?php
namespace DasRed\PhraseApp\Command;

use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\ConfigAwareInterface;
use DasRed\PhraseApp\ConfigAwareTrait;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\Zend\Console\ConsoleAwareInterface;
use DasRed\Zend\Console\ConsoleAwareTrait;
use DasRed\PhraseApp\Command\Exception\InvalidCommand;
use DasRed\PhraseApp\Command\Exception\InvalidCommandOperation;

class Factory implements ConfigAwareInterface, ConsoleAwareInterface
{
	use ConfigAwareTrait;
	use ConsoleAwareTrait;

	/**
	 *
	 * @param Config $config
	 */
	public function __construct(Config $config, AdapterInterface $console)
	{
		$this->setConfig($config)->setConsole($console);
	}

	/**
	 *
	 * @param array $arguments
	 * @return ExecutorAbstract
	 */
	public function factory(array $arguments)
	{
		$command = ucfirst(array_shift($arguments));
		$operation = ucfirst(array_shift($arguments));

		// command not found
		if (is_dir(__DIR__ . '/Executor/' . $command) === false)
		{
			throw new InvalidCommand(lcfirst($command));
		}

		$className = '\\DasRed\\PhraseApp\\Command\\Executor\\' . $command . '\\' . $operation;
		// List is reserved :/
		if ($operation == 'List')
		{
			$className = '\\DasRed\\PhraseApp\\Command\\Executor\\' . $command . '\\Fetch';
		}

		// find
		if (class_exists($className) === false)
		{
			throw new InvalidCommandOperation(lcfirst($command), lcfirst($operation));
		}

		return new $className($this->getConfig(), $this->getConsole(), $arguments);
	}
}