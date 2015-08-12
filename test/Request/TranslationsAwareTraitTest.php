<?php
namespace DasRedTest\PhraseApp\Request;

use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\Request\Translations;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request\TranslationsAwareTrait
 */
class TranslationsAwareTraitTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::getPhraseAppTranslations
	 */
	public function testGetSetConfig()
	{
		$config = new Config('a', 'de');

		$trait = $this->getMockBuilder('\DasRed\PhraseApp\Request\TranslationsAwareTrait')->setMethods(['getConfig'])->getMockForTrait();
		$trait->expects($this->any())->method('getConfig')->willReturn($config);

		$instance = $trait->getPhraseAppTranslations();
		$this->assertInstanceOf(Translations::class, $instance);
		$this->assertSame($instance, $trait->getPhraseAppTranslations());
		$this->assertSame($config, $instance->getConfig());
	}
}
