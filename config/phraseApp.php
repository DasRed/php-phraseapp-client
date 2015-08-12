<?php
use DasRed\PhraseApp\Config;

return [
	'phraseApp' => [
		'excludeNames' => [],
		'tagForContentChangeFromLocalToRemote' => 'newContent',
		'preferDirection' => Config::PREFER_REMOTE,

		'accessToken' => null,
		'projectId' => null,
		'localeDefault' => 'de-DE',
		'applicationName' => null
	],
];