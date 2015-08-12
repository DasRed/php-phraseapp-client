<?php
namespace DasRedTest\PhraseApp\Request;

use DasRed\PhraseApp\Request\Locales;
use DasRed\PhraseApp\Config;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request\Locales
 */
class LocalesTest extends \PHPUnit_Framework_TestCase
{
	protected $config;

	public function setUp()
	{
		parent::setUp();

		$this->config = new Config('b', 'de', 'appName', 'a');
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->config = null;
	}

	/**
	 * @covers ::create
	 */
	public function testCreateSuccess()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodPost'])->setConstructorArgs([$this->config])->getMock();
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
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodPost'])->setConstructorArgs([$this->config])->getMock();
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
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$locales->expects($this->once())->method('methodGet')->with('locales/')->willReturn([['code' => 'a'], ['code' => 'b']]);

		$this->assertEquals(['a', 'b'], $locales->fetch());
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetchFailed()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$locales->expects($this->once())->method('methodGet')->with('locales/')->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertEquals([], $locales->fetch());
	}
}
