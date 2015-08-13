<?php
namespace DasRed\PhraseApp\Command\Executor\Key;

use DasRed\PhraseApp\Command\Executor\KeyAbstract;
use Zend\Console\ColorInterface;

class Delete extends KeyAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$key = $this->getArguments()[0];

		if ($this->getPhraseAppKeys()->delete($key) === false)
		{
			$this->getConsole()->writeLine('Key ' . $key . ' can not be deleted.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
			return false;
		}

		$this->getConsole()->writeLine('Key ' . $key . ' deleted.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);

		return true;
	}

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::validateArguments()
	 */
	protected function validateArguments($arguments)
	{
		if (count($arguments) !== 1)
		{
			return false;
		}

		return parent::validateArguments($arguments);
	}
}