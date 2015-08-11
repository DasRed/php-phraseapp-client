<?php
namespace DasRed\PhraseApp\Translator;

use DasRed\Translation\Translator;
class PhraseApp extends Translator
{
	/**
	 *
	 * @var string
	 */
	const PREFIX = '[[__phrase_';

	/**
	 *
	 * @var string
	 */
	const SUFFIX = '__]]';

	/**
	 * (non-PHPdoc)
	 * @see \DasRed\Translation\Translator::__()
	 */
	public function __($key, array $parameters = [], $locale = null, $default = null, $parseBBCode = true)
	{
		return self::PREFIX . $key . self::SUFFIX;
	}
}