<?php
namespace DasRed\PhraseApp\Command\Executor\Key;

use DasRed\PhraseApp\Command\Executor\KeyAbstract;
use Zend\Console\ColorInterface;

class AddTag extends KeyAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$name = $this->getArguments()[0];
		$tag = $this->getArguments()[1];

		if ($this->getPhraseAppKeys()->addTag($name, $tag) === false)
		{
			$this->getConsole()->writeLine('Tag can not be added to key ' . $name . '.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
			return false;
		}

		$this->getConsole()->writeLine('Tag added to key ' . $name . '.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);

		return true;
	}

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::validateArguments()
	 */
	protected function validateArguments($arguments)
	{
		if (count($arguments) !== 2)
		{
			return false;
		}

		return parent::validateArguments($arguments);
	}
}