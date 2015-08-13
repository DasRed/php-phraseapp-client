<?php
use Zend\Console\Console;
use Zend\Console\ColorInterface;
use DasRed\Zend\Log\Logger\Console as Logger;
use DasRed\Zend\Console\Getopt;
use DasRed\Zend\Log\Writer\Console as ConsoleWriter;
use DasRed\PhraseApp\Synchronize\Files;
use DasRed\PhraseApp\Synchronize\Files\Type\Php;
use DasRed\PhraseApp\Version;
use DasRed\PhraseApp\Config;

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

	'preferDirection|d-s' => 'prefer direction for sync (remote, local). Default: remote',
	'exclude|x-s' => 'regex to exclude files. can be given multiple times: Default: []',
	'tagForContentChangeFromLocalToRemote|t-s' => 'tag for content change from local to remote. Default: newContent',

	'help|h' => 'Display this help message',
	'quiet|q' => 'Do not output any message',
	'version|V' => 'Display this application version'
]))->setOptions([
	Getopt::CONFIG_CUMULATIVE_PARAMETERS => true
]);

try
{
	$opt->parse();

	if ($opt->help)
	{
		throw new \Exception('wants help');
	}

	if (!$opt->version && count($opt->getRemainingArgs()) != 1)
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

	// set preferDirection
	if ($opt->preferDirection)
	{
		$config->setPreferDirection($opt->preferDirection);
	}

	// set tagForContentChangeFromLocalToRemote
	if ($opt->tagForContentChangeFromLocalToRemote)
	{
		$config->setTagForContentChangeFromLocalToRemote($opt->tagForContentChangeFromLocalToRemote);
	}
}
catch (\Exception $exception)
{
	$message = 'sourcePath' . PHP_EOL;
	$message .= PHP_EOL;
	$message .= $console->colorize('Arguments:', ColorInterface::YELLOW) . PHP_EOL;
	$message .= $console->colorize(' sourcePath', ColorInterface::GREEN) . '           Path to search in.' . PHP_EOL;

	echo $opt->getUsageMessage($message);

	exit(1);
}

// version
if ($opt->version)
{
	$console->writeLine('PHP PhraseApp Client - ' . basename($_SERVER['argv'][0], '.php') . ' ' . (new Version())->get() . ' by Marco Starker');
	exit(0);
}

// run
try
{
	// create logger
	$logger = (new Logger())->addWriter(new ConsoleWriter(Console::getInstance(), $opt->quiet ? ConsoleWriter::QUIET : ConsoleWriter::DEBUG));

	// create handler
	$exclude = [];
	if ($opt->exclude)
	{
		$exclude = is_array($opt->exclude) ? $opt->exclude : [
			$opt->exclude
		];
	}
	$handler = new Php($opt->getRemainingArgs()[0], $exclude);

	// create syncer
	$files = new Files($logger, $config);

	// config syncer
	$files->appendHandler($handler);

	if ($files->synchronize() === false)
	{
		$logger->always(PHP_EOL . PHP_EOL)->always($console->colorize('Synchronize failed without an specific reason', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED));
		exit(1);
	}
}
catch (\Exception $exception)
{
	$logger->always(PHP_EOL . PHP_EOL)->always($console->colorize($exception->getMessage(), ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED))
		->always(PHP_EOL)
		->always($console->colorize($exception->getTraceAsString(), ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED));
	exit(1);
}

exit(0);