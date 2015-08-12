<?php
namespace DasRedTest\PhraseApp\Request;

use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\Request\Locales;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request\LocalesAwareTrait
 */
class LocalesAwareTraitTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::getPhraseAppLocales
	 * @covers ::setPhraseAppLocales
	 */
	public function testGetSetPhraseAppLocales()
	{
		$config = new Config('pp', 'a', 'de');
		$instanceOther = new Locales($config);

		$trait = $this->getMockBuilder('\DasRed\PhraseApp\Request\LocalesAwareTrait')->setMethods(['getConfig'])->getMockForTrait();
		$trait->expects($this->any())->method('getConfig')->willReturn($config);

		$instance = $trait->getPhraseAppLocales();
		$this->assertInstanceOf(Locales::class, $instance);
		$this->assertSame($instance, $trait->getPhraseAppLocales());
		$this->assertSame($config, $instance->getConfig());

		$this->assertSame($trait, $trait->setPhraseAppLocales($instanceOther));
		$this->assertSame($instanceOther, $trait->getPhraseAppLocales());
	}
}
