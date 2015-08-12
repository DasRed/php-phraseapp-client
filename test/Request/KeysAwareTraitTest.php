<?php
namespace DasRedTest\PhraseApp\Request;

use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\Request\Keys;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request\KeysAwareTrait
 */
class KeysAwareTraitTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::getPhraseAppKeys
	 */
	public function testGetSetConfig()
	{
		$config = new Config('a', 'de');

		$trait = $this->getMockBuilder('\DasRed\PhraseApp\Request\KeysAwareTrait')->setMethods(['getConfig'])->getMockForTrait();
		$trait->expects($this->any())->method('getConfig')->willReturn($config);

		$instance = $trait->getPhraseAppKeys();
		$this->assertInstanceOf(Keys::class, $instance);
		$this->assertSame($instance, $trait->getPhraseAppKeys());
		$this->assertSame($config, $instance->getConfig());
	}
}
