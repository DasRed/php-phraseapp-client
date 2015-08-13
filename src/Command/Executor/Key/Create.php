<?php
namespace DasRed\PhraseApp\Command\Executor\Key;

use DasRed\PhraseApp\Command\Executor\KeyAbstract;
use Zend\Console\ColorInterface;

class Create extends KeyAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$tags = $this->getArguments();
		$name = array_shift($tags);
		$description = array_shift($tags);

		if ($this->getPhraseAppKeys()->create($name, $description, $tags) === false)
		{
			$this->getConsole()->writeLine('Key ' . $name . ' can not be created.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
			return false;
		}

		$this->getConsole()->writeLine('Key ' . $name . ' created.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);

		return true;
	}

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::validateArguments()
	 */
	protected function validateArguments($arguments)
	{
		if (count($arguments) < 1)
		{
			return false;
		}

		return parent::validateArguments($arguments);
	}
}