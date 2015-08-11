<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Locales;
/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Locales
 */
class LocalesTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers ::create
	 */
	public function testCreateSuccess()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodPost'])->disableOriginalConstructor()->getMock();
		$locales->expects($this->once())->method('methodPost')->with('locales/', [
			'locale' => ['name' => 'de-DE']
		])->willReturn([]);

		$this->assertTrue($locales->create('de-DE'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateFailed()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodPost'])->disableOriginalConstructor()->getMock();
		$locales->expects($this->once())->method('methodPost')->with('locales/', [
			'locale' => ['name' => 'de-DE']
		])->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($locales->create('de-DE'));
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetchSuccess()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$locales->expects($this->once())->method('methodGet')->with('locales/')->willReturn([['code' => 'a'], ['code' => 'b']]);

		$this->assertEquals(['a', 'b'], $locales->fetch());
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetchFailed()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$locales->expects($this->once())->method('methodGet')->with('locales/')->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertEquals([], $locales->fetch());
	}
}
