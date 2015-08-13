<?php
namespace DasRed\PhraseApp\Command\Executor\Key;

use DasRed\PhraseApp\Command\Executor\KeyAbstract;
use Zend\Console\ColorInterface;

class Update extends KeyAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$tags = $this->getArguments();
		$key = array_shift($tags);
		$name = array_shift($tags);
		$description = array_shift($tags);

		if (count($tags) === 0)
		{
			$tags = null;
		}

		if ($this->getPhraseAppKeys()->update($key, $name, $description, $tags) === false)
		{
			$this->getConsole()->writeLine('Key ' . $key . ' can not be updated.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
			return false;
		}

		$this->getConsole()->writeLine('Key ' . $key . ' updated.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);

		return true;
	}

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::validateArguments()
	 */
	protected function validateArguments($arguments)
	{
		if (count($arguments) < 2)
		{
			return false;
		}

		return parent::validateArguments($arguments);
	}
}