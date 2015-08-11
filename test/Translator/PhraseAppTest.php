<?php
namespace DasRedTest\PhraseApp\Translator;

use DasRed\PhraseApp\Translator\PhraseApp;
use DasRed\Parser\BBCode;
use Zend\Log\Logger;
use Zend\Log\Writer\Mock;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Translator\PhraseApp
 */
class PhraseAppTest extends \PHPUnit_Framework_TestCase
{
	protected $logger;

	protected $logWriter;

	protected $markupRenderer;

	protected $path;

	public function setUp()
	{
		parent::setUp();

		$this->logWriter = new Mock();
		$this->logger = new Logger();
		$this->logger->addWriter($this->logWriter);
		$this->markupRenderer = new BBCode();
		$this->path = __DIR__ . '/translation';
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	public function tearDown()
	{
		parent::tearDown();

		$this->logger = null;
		$this->logWriter = null;
		$this->markupRenderer = null;
		$this->path = null;
	}

	public function dataProvider__()
	{
		return [
			['de-DE', 'de-DE', 'test.a', [], null, null, true, true, '[[__phrase_test.a__]]'],
			['de-DE', 'de-DE', 'test.key', [], null, null, true, true, '[[__phrase_test.key__]]'],
			['de-DE', 'de-DE', 'test.param1', [], null, null, true, true, '[[__phrase_test.param1__]]'],
			['de-DE', 'de-DE', 'test.param2', [], null, null, true, true, '[[__phrase_test.param2__]]'],
			['de-DE', 'de-DE', 'other.a', [], null, null, true, true, '[[__phrase_other.a__]]'],
			['de-DE', 'de-DE', 'other.key', [], null, null, true, true, '[[__phrase_other.key__]]'],
			['de-DE', 'de-DE', 'other.a.b.c', [], null, null, true, true, '[[__phrase_other.a.b.c__]]'],

			['de-DE', 'de-DE', 'other.bb', [], null, null, true, true, '[[__phrase_other.bb__]]'],
			['de-DE', 'de-DE', 'other.bb', [], null, null, false, true, '[[__phrase_other.bb__]]'],
			['de-DE', 'de-DE', 'other.bb', [], null, null, true, false, '[[__phrase_other.bb__]]'],
			['de-DE', 'de-DE', 'other.bb', [], null, null, false, false, '[[__phrase_other.bb__]]'],

			['de-DE', 'de-DE', 'other.nuff', [], 'en-US', null, true, true, '[[__phrase_other.nuff__]]'],
			['de-DE', 'de-DE', 'other.lol', [], 'en-US', null, true, true, '[[__phrase_other.lol__]]'],

			['de-DE', 'de-DE', 'test.a', ['c' => 'd'], null, null, true, true, '[[__phrase_test.a__]]'],
			['de-DE', 'de-DE', 'test.param1', ['p1' => 'jo'], null, null, true, true, '[[__phrase_test.param1__]]'],

			['de-DE', 'de-DE', 'testparam1', ['p1' => 'jo'], null, null, true, false, '[[__phrase_testparam1__]]'],
			['de-DE', 'de-DE', 'testparam1', ['p1' => 'jo'], null, 'narfnarfnarfnarf', true, false, '[[__phrase_testparam1__]]'],

			['de-DE', 'de-DE', 'other.a', [], 'en-US', null, false, false, '[[__phrase_other.a__]]'],

			['fr-FR', 'fr-CH', 'nuff.narf', [], null, null, false, false, '[[__phrase_nuff.narf__]]'],
			['fr-FR', 'fr-CH', 'nuff.lol', [], null, null, false, false, '[[__phrase_nuff.lol__]]'],
			['fr-FR', 'fr-CH', 'nuff.haha', [], null, null, false, false, '[[__phrase_nuff.haha__]]'],
			['fr-FR', 'fr-CH', 'nuff.lol', [], 'de-DE', null, false, false, '[[__phrase_nuff.lol__]]'],

			['fr-FR', 'fr-CH', 'other.key', [], 'de-DE', null, false, false, '[[__phrase_other.key__]]'],

			['ru-RU', 'ru-RU', 'test/a/nuff/module.nuff', [], null, null, false, false, '[[__phrase_test/a/nuff/module.nuff__]]'],

			['it-IT', 'it-IT', 'file/test_0/text_0.1.narf', [], null, null, false, false, '[[__phrase_file/test_0/text_0.1.narf__]]'],
			['it-IT', 'it-IT', 'file/test.1/text.1.1.lol', [], null, null, false, false, '[[__phrase_file/test.1/text.1.1.lol__]]'],
		];
	}

	/**
	 * @covers ::__
	 * @dataProvider dataProvider__
	 */
	public function test__($localeCurrent, $localeDefault, $key, array $parameters, $locale, $default, $parserInjected, $parseBBCode, $expected)
	{
		$translation = new PhraseApp($localeCurrent, $this->path, $localeDefault, $this->logger);
		if ($parserInjected)
		{
			$translation->setMarkupRenderer($this->markupRenderer);
		}

		$this->assertEquals($expected, $translation->__($key, $parameters, $locale, $default, $parseBBCode));
	}
}