<?php
namespace DasRed\PhraseApp\Command\Executor\Translation;

use DasRed\PhraseApp\Command\Executor\TranslationAbstract;
use Zend\Console\ColorInterface;

class Store extends TranslationAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$locale = $this->getArguments()[0];
		$key = $this->getArguments()[1];
		$content = $this->getArguments()[2];

		if ($this->getPhraseAppTranslations()->store($locale, $key, $content) === false)
		{
			$this->getConsole()->writeLine('Content can not be setted to locale ' . $locale . ' for the key ' . $key . '.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
			return false;
		}

		$this->getConsole()->writeLine('Content was setted to locale ' . $locale . ' for the key ' . $key . '.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);

		return true;
	}

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::validateArguments()
	 */
	protected function validateArguments($arguments)
	{
		if (count($arguments) !== 3)
		{
			return false;
		}

		return parent::validateArguments($arguments);
	}
}