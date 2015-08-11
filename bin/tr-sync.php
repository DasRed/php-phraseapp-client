<?php
use Zend\Config\Factory;
use Zend\Console\Console;
use Zend\Console\ColorInterface;
use DasRed\Zend\Log\Logger\Console as Logger;
use DasRed\Zend\Console\Getopt;
use DasRed\Zend\Log\Writer\Console as ConsoleWriter;
use DasRed\PhraseApp\Synchronize\Files;
use DasRed\PhraseApp\Synchronize\Files\Type\Php;
use DasRed\PhraseApp\Version;
use Zend\Config\Config;

set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext)
{
	throw new Exception($errstr, $errno);
});

$autoloader = null;
foreach ([
	// Local install
	__DIR__ . '/../vendor/autoload.php',
	// Root project is current working directory
	getcwd() . '/vendor/autoload.php',
	// Relative to composer install
	__DIR__ . '/../../../autoload.php'
] as $autoloadFile)
{
	if (file_exists($autoloadFile) === true)
	{
		$autoloader = require $autoloadFile;
		break;
	}
}

// autoload not found... abort
if ($autoloader === null)
{
	fwrite(STDERR, 'Unable to setup autoloading; aborting\n');
	exit(2);
}

// consoleoptions
$console = Console::getInstance();
$opt = (new Getopt(
	[
		'authToken|a-s' => 'project auth token',
		'email|e-s' => 'email to login',
		'password|p-s' => 'password to login',
		'exclude|x-s' => 'regex to exclude files. can be given multiple times: Default: []',
		'tagForContentChangeFromLocalToRemote|t-s' => 'tag for content change from local to remote. Default: newContent',
		'preferDirection|d-s' => 'prefer direction for sync (remote, local). Default: remote',
		'localeDefault|l-s' => 'default locale. Default de-DE',
		'config|c-s' => 'config file to use. Default __DIR__/../config/phraseApp.php',
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

	if (! $opt->version && count($opt->getRemainingArgs()) != 1)
	{
		throw new \Exception('missing remaining args');
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

// create the config
$config = new Config(Factory::fromFile(__DIR__ . '/../config/phraseApp.php')['phraseApp'], true);

if ($opt->config)
{
	$configCli = Factory::fromFile($opt->config, true);
	if ($configCli->offsetExists('phraseApp'))
	{
		$configCli = $configCli->phraseApp;
	}
	$config->merge($configCli);
}

if ($opt->authToken)
{
	$config->authToken = $opt->authToken;
}
if ($opt->email)
{
	$config->userEmail = $opt->email;
}
if ($opt->password)
{
	$config->userPassword = $opt->password;
}
if ($opt->exclude)
{
	$config->excludeNames = is_array($opt->exclude) ? $opt->exclude : [
		$opt->exclude
	];
}
if ($opt->tagForContentChangeFromLocalToRemote)
{
	$config->tagForContentChangeFromLocalToRemote = $opt->tagForContentChangeFromLocalToRemote;
}
if ($opt->preferDirection)
{
	$config->preferDirection = $opt->preferDirection;
}
if ($opt->localeDefault)
{
	$config->localeDefault = $opt->localeDefault;
}

// path
$config->path = $opt->getRemainingArgs()[0];

// run
try
{
	// create logger
	$logger = (new Logger())->addWriter(new ConsoleWriter(Console::getInstance(), $opt->quiet ? ConsoleWriter::QUIET : ConsoleWriter::DEBUG));

	// create handler
	$handler = new Php($config->path, $config->excludeNames->toArray());

	// create syncer
	$files = new Files($logger, $config->baseUrl, $config->authToken, $config->userEmail, $config->userPassword, $config->localeDefault);

	// config syncer
	$files->appendHandler($handler)->setPreferDirection($config->preferDirection);
	if ($config->tagForContentChangeFromLocalToRemote)
	{
		$files->setTagForContentChangeFromLocalToRemote($config->tagForContentChangeFromLocalToRemote);
	}

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
