<?php
namespace DasRed\PhraseApp\Command\Executor\Key;

use DasRed\PhraseApp\Command\Executor\KeyAbstract;
use Zend\Console\ColorInterface;

class Fetch extends KeyAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$count = $this->getPhraseAppKeys()->getCollection()->count();
		$this->getConsole()->write('Found ');
		$this->getConsole()->write($count, ColorInterface::LIGHT_GREEN);
		$this->getConsole()->writeLine(' keys.');

		$this->getPhraseAppKeys()->getCollection()->each(function($entry)
		{
			$this->getConsole()->write(' - ');
			$this->getConsole()->write($entry['name'], ColorInterface::LIGHT_GREEN);
			$this->getConsole()->writeLine('       ' . $entry['description']);
		});

		return true;
	}

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::validateArguments()
	 */
	protected function validateArguments($arguments)
	{
		return count($arguments) === 0;
	}
}