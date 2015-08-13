<?php
namespace DasRed\PhraseApp\Command\Executor\Locale;

use DasRed\PhraseApp\Command\Executor\LocaleAbstract;
use Zend\Console\ColorInterface;

class Fetch extends LocaleAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$count = $this->getPhraseAppLocales()->getCollection()->count();
		$this->getConsole()->write('Found ');
		$this->getConsole()->write($count, ColorInterface::LIGHT_GREEN);
		$this->getConsole()->writeLine(' locales.');

		$this->getPhraseAppLocales()->getCollection()->each(function($entry)
		{
			$this->getConsole()->write(' - ');
			$this->getConsole()->write($entry['code'], ColorInterface::LIGHT_GREEN);
			$this->getConsole()->writeLine(' ' . $entry['name']);
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