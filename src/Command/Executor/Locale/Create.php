<?php
namespace DasRed\PhraseApp\Command\Executor\Locale;

use DasRed\PhraseApp\Command\Executor\LocaleAbstract;
use Zend\Console\ColorInterface;

class Create extends LocaleAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$arguments = $this->getArguments();
		$locale = array_shift($arguments);
		$localeSource = array_shift($arguments);

		if ($this->getPhraseAppLocales()->create($locale, $localeSource) === false)
		{
			$this->getConsole()->writeLine('Locale ' . $locale . ' can not be created.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
			return false;
		}

		$this->getConsole()->writeLine('Locale ' . $locale . ' created.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);

		return true;
	}

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\PhraseApp\Command\ExecutorAbstract::validateArguments()
	 */
	protected function validateArguments($arguments)
	{
		if (count($arguments) < 1 || count($arguments) > 2)
		{
			return false;
		}

		return parent::validateArguments($arguments);
	}
}