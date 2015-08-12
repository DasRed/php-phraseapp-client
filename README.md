# php-phraseapp-client

PHP PhraseApp Client for [PhraseApp](https://phraseapp.com) and [API Version 2](http://docs.phraseapp.com/api/v2/).

# CLI Usage
## Usage
bin/tr-sync [options] sourcePath

## Arguments
sourcePath           Path to search in.

## Options
```
--projectId (-p) <string>                                 project id
--accessToken (-a) <string>                               user access token
--localeDefault (-l) <string>                             default locale. Default de-DE
--applicationName (-n) [ <string> ]                       name of your application. Default PHP PhraseApp Client
--preferDirection (-d) [ <string> ]                       prefer direction for sync (remote, local). Default: remote
--exclude (-x) [ <string> ]                               regex to exclude files. can be given multiple times: Default: []
--tagForContentChangeFromLocalToRemote (-t) [ <string> ]  tag for content change from local to remote. Default: newContent
--help (-h)                                               Display this help message
--quiet (-q)                                              Do not output any message
--version (-V)                                            Display this application version
```

# PHP Usage for Synchronize
```php
use DasRed\Zend\Log\Writer\Console as Writer;
use DasRed\Zend\Log\Logger\Console as Logger;
use Zend\Console\Console;
use DasRed\PhraseApp\Synchronize\Files\Type\Php;
use DasRed\PhraseApp\Synchronize\Files;
use DasRed\PhraseApp\Synchronize;
use DasRed\PhraseApp\Config;

// create logger
$writer = new Writer(Console::getInstance(), Writer::DEBUG);
$logger = new Logger();
$logger->addWriter($writer);

// create the config for synchronizer and co
$projectId = 'This is the project id.';
$accessToken = 'This is the authentication token for your user.';
$localeDefault = 'This is your default or main locale.';
$config = new Config($projectId, $accessToken, $localeDefault);

// This is the user agent which will reported to PhraseApp http://docs.phraseapp.com/api/v2/#identification-via-user-agent
$config->setApplicationName('Fancy Application Name (nuff@example.com)');

// set the direction, which is prefered. If all from PhraseApp is always correct, then REMOTE ist prefered. Otherwise LOCAL
$config->setPreferDirection(Synchronize::PREFER_REMOTE);

// set a default translation key tag which will be setted on translations keys, which will be written to phraseapp
$config->setTagForContentChangeFromLocalToRemote('newContent');

// create the synchronizer for many files
$synchronizer = new Files($logger, $config);

// create and append the handler for file loading and writing
$path = 'This is the path to translation in which the sub pathes defines the locales.';
$excludeNames = ['This is an array of regex to exclude if it match with by file name and file path'];
$handler = new Php($path, $excludeNames);
$synchronizer->appendHandler($handler);


// synchronize everything
if ($synchronizer->synchronize() === false)
{
	throw new \Exception();
}
```

# PHP Usage for Inline Translation of PhraseApp
Instead of using the original translator from [\DasRed\Translation\Translator](https://github.com/DasRed/translation/blob/master/src/Translator.php) use the [Translator (\DasRed\PhraseApp\Translator\PhraseApp)](https://github.com/DasRed/php-phraseapp-client/blob/master/src/Translator/PhraseApp.php) in this client:
```php
// instead of
$translator = new \DasRed\Translation\Translator('de-DE', __DIR__);

// use
$translator = new \DasRed\PhraseApp\Translator\PhraseApp('de-DE', __DIR__);
```
and then do the rest of the [explanation from PhraseApp](http://docs.phraseapp.com/guides/in-context-editor/). The inline key prefix will be "{{\_\_phrase\_" and the suffix will be "\_\_}}".