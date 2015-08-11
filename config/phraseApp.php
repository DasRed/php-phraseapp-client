<?php
use DasRed\PhraseApp\Synchronize;
return [
	'phraseApp' => [
		'authToken' => null,
		'path' => null,
		'baseUrl' => 'https://phraseapp.com/api/v1/',
		'userEmail' => '',
		'userPassword' => '',
		'excludeNames' => [],
		'tagForContentChangeFromLocalToRemote' => 'newContent',
		'preferDirection' => Synchronize::PREFER_REMOTE,
		'localeDefault' => 'de-DE',
	],
];