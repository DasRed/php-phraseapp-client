<?php
use DasRed\PhraseApp\Synchronize;
return [
	'phraseApp' => [
		'path' => null,
		'excludeNames' => [],
		'tagForContentChangeFromLocalToRemote' => 'newContent',
		'preferDirection' => Synchronize::PREFER_REMOTE,
		'localeDefault' => 'de-DE',

		'baseUrl' => 'https://api.phraseapp.com/api/v2/',
		'applicationName' => 'PHP PhraseApp Client (https://github.com/DasRed/php-phraseapp-client)',
		'accessToken' => ''
	],
];