<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Config;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\ConfigAwareTrait
 */
class ConfigAwareTraitTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::getConfig
	 * @covers ::setConfig
	 */
	public function testGetSetConfig()
	{
		$configA = new Config('a', 'de');
		$configB = new Config('a', 'de');

		$trait = $this->getMockBuilder('\DasRed\PhraseApp\ConfigAwareTrait')->setMethods([])->getMockForTrait();

		$this->assertNull($trait->getConfig());
		$this->assertSame($trait, $trait->setConfig($configA));
		$this->assertSame($configA, $trait->getConfig());
		$this->assertSame($trait, $trait->setConfig($configB));
		$this->assertSame($configB, $trait->getConfig());
	}
}
