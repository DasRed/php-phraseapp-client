<?php
namespace DasRed\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception;

class FailureStoreContentByTag extends Exception
{

	/**
	 *
	 * @param string $keyLocalToStore
	 */
	public function __construct($keyLocalToStore)
	{
		parent::__construct('Can not store translation content for translation key "' . $keyLocalToStore . '" because the tag for content change from local to remote can not be setted.');
	}
}
