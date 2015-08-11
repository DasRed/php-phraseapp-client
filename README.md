# php-phraseapp-client

PHP PhraseApp Client for [PhraseApp](https://phraseapp.com) and [API Version 1](http://docs.phraseapp.com/api/v1/).

# CLI Usage
## Usage
php bin/tr-sync.php [options] sourcePath

## Arguments
sourcePath           Path to search in.

## Options
```
--authToken (-a) [ <string> ]                             project auth token
--email (-e) [ <string> ]                                 email to login
--password (-p) [ <string> ]                              password to login
--exclude (-x) [ <string> ]                               regex to exclude files. can be given multiple times: Default: []
--tagForContentChangeFromLocalToRemote (-t) [ <string> ]  tag for content change from local to remote. Default: newContent
--preferDirection (-d) [ <string> ]                       prefer direction for sync (remote, local). Default: remote
--localeDefault (-l) [ <string> ]                         default locale. Default de-DE
--config (-c) [ <string> ]                                config file to use. Default __DIR__/../config/phraseApp.php
--help (-h)                                               Display this help message
--quiet (-q)                                              Do not output any message
--version (-V)                                            Display this application version
```

# PHP Usage
```php
use DasRed\Zend\Log\Writer\Console as Writer;
use DasRed\Zend\Log\Logger\Console as Logger;
use Zend\Console\Console;
use DasRed\PhraseApp\Synchronize\Files\Type\Php;
use DasRed\PhraseApp\Synchronize\Files;
use DasRed\PhraseApp\Synchronize;

// create logger
$writer = new Writer(Console::getInstance(), Writer::DEBUG);
$logger = new Logger();
$logger->addWriter($writer);

// create the synchronizer for many files
$phraseAppBaseUrl = 'https://phraseapp.com/api/v1/';
$authToken = 'This is the Authentication Token from Project for API V1';
$userEmail = 'This is the user email to login';
$userPassword = 'This is the user password to login.';
$localeDefault = 'de-DE';
$synchronizer = new Files($logger, $phraseAppBaseUrl, $authToken, $userEmail, $userPassword, $localeDefault);

// create and append the handler for file loading and writing
$path = 'This is the path to translation with locales as sub path';
$excludeNames = ['This is an array of regex to exclude if it match with by file name and file path'];
$handler = new Php($path, $excludeNames);
$synchronizer->appendHandler($handler);

// set the direction, which is prefered. If all from PhraseApp is always correct, then REMOTE ist prefered. Otherwise LOCAL
$synchronizer->setPreferDirection(Synchronize::PREFER_REMOTE);

// set a default translation key tag which will be setted on translations keys, which will be written to phraseapp
$synchronizer->setTagForContentChangeFromLocalToRemote('newContent');

// synchronize everything
if ($synchronizer->synchronize() === false)
{
	throw new \Exception();
}
```
