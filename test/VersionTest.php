<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Version;
/**
 * @coversDefaultClass \DasRed\PhraseApp\Version
 */
class VersionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers ::get
	 */
	public function testGet()
	{
		$this->assertSame('1.0.0', (new Version())->get());
	}
}