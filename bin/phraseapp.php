<?php
use Zend\Console\Console;
use DasRed\PhraseApp\Config;
use Zend\Console\ColorInterface;
use DasRed\PhraseApp\Version;
use DasRed\Zend\Console\Getopt;
use DasRed\PhraseApp\Command\Factory;
use DasRed\PhraseApp\Command\Exception\InvalidArguments;

set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext)
{
	throw new Exception($errstr, $errno);
});

require_once __DIR__ . '/../autoload.php';

// consoleoptions
$console = Console::getInstance();
$opt = (new Getopt([
	'projectId|p=s' => 'project id',
	'accessToken|a=s' => 'user access token',
	'localeDefault|l=s' => 'default locale. Default de-DE',
	'applicationName|n-s' => 'name of your application. Default PHP PhraseApp Client',

	'help|h' => 'Display this help message',
	'version|V' => 'Display this application version'
]))->setOptions([
	Getopt::CONFIG_CUMULATIVE_PARAMETERS => true
]);

$message = 'operation' . PHP_EOL;
$message .= PHP_EOL;

$message .= $console->colorize('locale Operations:', ColorInterface::YELLOW) . PHP_EOL;
$message .= $console->colorize(' locale create <locale>', ColorInterface::GREEN) . '   creates the given locale.' . PHP_EOL;
$message .= $console->colorize(' locale list', ColorInterface::GREEN) . '              list all locales' . PHP_EOL;

$message .= PHP_EOL;
$message .= $console->colorize('key Operations:', ColorInterface::YELLOW) . PHP_EOL;
$message .= $console->colorize(' key addTag <key> <tag>', ColorInterface::GREEN) . '                            add the tag to the key.' . PHP_EOL;
$message .= $console->colorize(' key create <name> [description] [tag ...]', ColorInterface::GREEN) . '         create a new key.' . PHP_EOL;
$message .= $console->colorize(' key delete <key>', ColorInterface::GREEN) . '                                  deletes the key.' . PHP_EOL;
$message .= $console->colorize(' key list', ColorInterface::GREEN) . '                                          list the key.' . PHP_EOL;
$message .= $console->colorize(' key update <key> <name> [description] [tag ...]', ColorInterface::GREEN) . '   updates the key.' . PHP_EOL;

$message .= PHP_EOL;
$message .= $console->colorize('translation Operations:', ColorInterface::YELLOW) . PHP_EOL;
$message .= $console->colorize(' translation store <locale> <key> <content>', ColorInterface::GREEN) . '   set content of a key for a locale.' . PHP_EOL;

try
{
	$opt->parse();

	if ($opt->help)
	{
		throw new \Exception('wants help');
	}

	if (!$opt->version && count($opt->getRemainingArgs()) < 2)
	{
		throw new \Exception('missing remaining args');
	}

	// create config
	$config = new Config($opt->projectId, $opt->accessToken, $opt->localeDefault);

	// set application name
	if ($opt->applicationName)
	{
		$config->setApplicationName($opt->applicationName);
	}
}
catch (\Exception $exception)
{
	echo $opt->getUsageMessage($message);
	exit(1);
}

// version
if ($opt->version)
{
	$console->writeLine('PHP PhraseApp Client - ' . basename($_SERVER['argv'][0], '.php') . ' ' . (new Version())->get() . ' by Marco Starker');
	exit(0);
}

try
{
	if ((new Factory($config, $console))->factory($opt->getRemainingArgs())->execute() === false)
	{
		$console->writeLine('Operation failed.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
		exit(1);
	}
}
catch (InvalidArguments $exception)
{
	$console->writeLine('Invalid arguments for operation.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
	echo PHP_EOL . $opt->getUsageMessage($message);
	exit(1);
}
catch (\Exception $exception)
{
	$console->writeLine($exception->getMessage(), ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
	exit(1);
}

exit(0);