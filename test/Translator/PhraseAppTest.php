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
			['de-DE', 'de-DE', 'test.a', [], null, null, true, true, PhraseApp::PREFIX . 'test.a' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'test.key', [], null, null, true, true, PhraseApp::PREFIX . 'test.key' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'test.param1', [], null, null, true, true, PhraseApp::PREFIX . 'test.param1' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'test.param2', [], null, null, true, true, PhraseApp::PREFIX . 'test.param2' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'other.a', [], null, null, true, true, PhraseApp::PREFIX . 'other.a' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'other.key', [], null, null, true, true, PhraseApp::PREFIX . 'other.key' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'other.a.b.c', [], null, null, true, true, PhraseApp::PREFIX . 'other.a.b.c' . PhraseApp::SUFFIX],

			['de-DE', 'de-DE', 'other.bb', [], null, null, true, true, PhraseApp::PREFIX . 'other.bb' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'other.bb', [], null, null, false, true, PhraseApp::PREFIX . 'other.bb' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'other.bb', [], null, null, true, false, PhraseApp::PREFIX . 'other.bb' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'other.bb', [], null, null, false, false, PhraseApp::PREFIX . 'other.bb' . PhraseApp::SUFFIX],

			['de-DE', 'de-DE', 'other.nuff', [], 'en-US', null, true, true, PhraseApp::PREFIX . 'other.nuff' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'other.lol', [], 'en-US', null, true, true, PhraseApp::PREFIX . 'other.lol' . PhraseApp::SUFFIX],

			['de-DE', 'de-DE', 'test.a', ['c' => 'd'], null, null, true, true, PhraseApp::PREFIX . 'test.a' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'test.param1', ['p1' => 'jo'], null, null, true, true, PhraseApp::PREFIX . 'test.param1' . PhraseApp::SUFFIX],

			['de-DE', 'de-DE', 'testparam1', ['p1' => 'jo'], null, null, true, false, PhraseApp::PREFIX . 'testparam1' . PhraseApp::SUFFIX],
			['de-DE', 'de-DE', 'testparam1', ['p1' => 'jo'], null, 'narfnarfnarfnarf', true, false, PhraseApp::PREFIX . 'testparam1' . PhraseApp::SUFFIX],

			['de-DE', 'de-DE', 'other.a', [], 'en-US', null, false, false, PhraseApp::PREFIX . 'other.a' . PhraseApp::SUFFIX],

			['fr-FR', 'fr-CH', 'nuff.narf', [], null, null, false, false, PhraseApp::PREFIX . 'nuff.narf' . PhraseApp::SUFFIX],
			['fr-FR', 'fr-CH', 'nuff.lol', [], null, null, false, false, PhraseApp::PREFIX . 'nuff.lol' . PhraseApp::SUFFIX],
			['fr-FR', 'fr-CH', 'nuff.haha', [], null, null, false, false, PhraseApp::PREFIX . 'nuff.haha' . PhraseApp::SUFFIX],
			['fr-FR', 'fr-CH', 'nuff.lol', [], 'de-DE', null, false, false, PhraseApp::PREFIX . 'nuff.lol' . PhraseApp::SUFFIX],

			['fr-FR', 'fr-CH', 'other.key', [], 'de-DE', null, false, false, PhraseApp::PREFIX . 'other.key' . PhraseApp::SUFFIX],

			['ru-RU', 'ru-RU', 'test/a/nuff/module.nuff', [], null, null, false, false, PhraseApp::PREFIX . 'test/a/nuff/module.nuff' . PhraseApp::SUFFIX],

			['it-IT', 'it-IT', 'file/test_0/text_0.1.narf', [], null, null, false, false, PhraseApp::PREFIX . 'file/test_0/text_0.1.narf' . PhraseApp::SUFFIX],
			['it-IT', 'it-IT', 'file/test.1/text.1.1.lol', [], null, null, false, false, PhraseApp::PREFIX . 'file/test.1/text.1.1.lol' . PhraseApp::SUFFIX],
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