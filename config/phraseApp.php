<?php
use DasRed\PhraseApp\Config;

return [
	'phraseApp' => [
		'path' => null,
		'excludeNames' => [],
		'tagForContentChangeFromLocalToRemote' => 'newContent',
		'preferDirection' => Config::PREFER_REMOTE,
		'localeDefault' => 'de-DE',

		'accessToken' => ''
	],
];