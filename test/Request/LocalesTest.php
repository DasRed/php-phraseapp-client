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

		$this->config = new Config('pp', 'b', 'de');
		$this->config->setApplicationName('appName')->setBaseUrl('a');
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
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['load', 'methodPost'])->setConstructorArgs([$this->config])->getMock();
		$locales->expects($this->any())->method('load')->with()->willReturn([]);
		$locales->expects($this->once())->method('methodPost')->with(Locales::URL_API,
			['name' => 'de-DE', 'code' => 'de-DE']
		)->willReturn(
			['name' => 'de-DE', 'code' => 'de-DE', 'id' => '43q2iohf89rwrt']
		);

		$this->assertTrue($locales->create('de-DE'));
		$this->assertCount(1, $locales->getCollection());
		$this->assertEquals(['name' => 'de-DE', 'code' => 'de-DE', 'id' => '43q2iohf89rwrt'], $locales->getCollection()->get('de-DE'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateFailed()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['load', 'methodPost'])->setConstructorArgs([$this->config])->getMock();
		$locales->expects($this->any())->method('load')->with()->willReturn([]);
		$locales->expects($this->once())->method('methodPost')->with(Locales::URL_API,
			['name' => 'de-DE', 'code' => 'de-DE']
		)->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($locales->create('de-DE'));
		$this->assertCount(0, $locales->getCollection());
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetchWithData()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['load', 'methodGet'])->setConstructorArgs([$this->config])->getMock();
		$locales->expects($this->any())->method('load')->with()->willReturn([['code' => 'a', 'id' => '43q2iohf89rwrt'], ['code' => 'b']]);
		$locales->expects($this->never())->method('methodGet');

		$this->assertEquals(['a', 'b'], $locales->fetch());
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetchWithoutData()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['load', 'methodGet'])->setConstructorArgs([$this->config])->getMock();
		$locales->expects($this->any())->method('load')->with()->willReturn([]);
		$locales->expects($this->never())->method('methodGet');

		$this->assertEquals([], $locales->fetch());
	}

	/**
	 * @covers ::getIdKey
	 */
	public function testGetIdKey()
	{
		$locales = new Locales($this->config);

		$reflectionMethod = new \ReflectionMethod($locales, 'getIdKey');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('code', $reflectionMethod->invoke($locales));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadSuccess()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$locales->expects($this->once())->method('methodGet')->with(Locales::URL_API)->willReturn([['code' => 'a'], ['code' => 'b']]);

		$reflectionMethod = new \ReflectionMethod($locales, 'load');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([['code' => 'a'], ['code' => 'b']], $reflectionMethod->invoke($locales));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadFailed()
	{
		$locales = $this->getMockBuilder(Locales::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$locales->expects($this->once())->method('methodGet')->with(Locales::URL_API)->willThrowException(new \DasRed\PhraseApp\Exception());

		$reflectionMethod = new \ReflectionMethod($locales, 'load');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([], $reflectionMethod->invoke($locales));
	}
}
