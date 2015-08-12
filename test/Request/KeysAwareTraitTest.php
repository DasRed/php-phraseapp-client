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
	 * @covers ::setPhraseAppKeys
	 */
	public function testGetSetPhraseAppKeys()
	{
		$config = new Config('pp', 'a', 'de');
		$instanceOther = new Keys($config);

		$trait = $this->getMockBuilder('\DasRed\PhraseApp\Request\KeysAwareTrait')->setMethods(['getConfig'])->getMockForTrait();
		$trait->expects($this->any())->method('getConfig')->willReturn($config);

		$instance = $trait->getPhraseAppKeys();
		$this->assertInstanceOf(Keys::class, $instance);
		$this->assertSame($instance, $trait->getPhraseAppKeys());
		$this->assertSame($config, $instance->getConfig());

		$this->assertSame($trait, $trait->setPhraseAppKeys($instanceOther));
		$this->assertSame($instanceOther, $trait->getPhraseAppKeys());
	}
}
