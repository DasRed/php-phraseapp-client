<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Request\Translations;
use DasRed\PhraseApp\Config;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request\Translations
 */
class TranslationsTest extends \PHPUnit_Framework_TestCase
{
	protected $config;

	protected $loadWithData = [
		1 => ['id' => 1, 'content' => 'c1', 'locale' => ['id' => 10, 'code' => 'de-DE'], 'key' => ['id' => 15, 'name' => 'c1key']],
		2 => ['id' => 2, 'content' => 'c2', 'locale' => ['id' => 20, 'code' => 'de-DE'], 'key' => ['id' => 25, 'name' => 'c2key']],
		3 => ['id' => 3, 'content' => 'c3', 'locale' => ['id' => 30, 'code' => 'de-DE'], 'key' => ['id' => 35, 'name' => 'c3key']],
		4 => ['id' => 4, 'content' => 'c4', 'locale' => ['id' => 40, 'code' => 'de-DE'], 'key' => ['id' => 45, 'name' => 'c4key']],
		5 => ['id' => 5, 'content' => 'c5', 'locale' => ['id' => 50, 'code' => 'ru-RU'], 'key' => ['id' => 55, 'name' => 'c5key']],
	];

	public function setUp()
	{
		parent::setUp();

		$this->config = new Config('pp', 'b', 'de', 'appName', 'a');
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
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['load', 'methodPost'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$translations->expects($this->once())->method('methodPost')->with(Translations::URL_API)->willReturn(
			['id' => 6, 'content' => 'c6', 'locale' => ['id' => 60, 'code' => 'ru-RU'], 'key' => ['id' => 65, 'name' => 'c6key']]
		);

		$reflectionMethod = new \ReflectionMethod($translations, 'create');
		$reflectionMethod->setAccessible(true);

		$this->assertTrue($reflectionMethod->invoke($translations, '60', 65, 'c6'));
		$this->assertCount(6, $translations->getCollection());
		$this->assertEquals(
			['id' => 6, 'content' => 'c6', 'locale' => ['id' => 60, 'code' => 'ru-RU'], 'key' => ['id' => 65, 'name' => 'c6key']]
		, $translations->getCollection()->get(6));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateSuccessFailed()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['load', 'methodPost'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$translations->expects($this->once())->method('methodPost')->with(Translations::URL_API)->willThrowException(new \DasRed\PhraseApp\Exception());

		$reflectionMethod = new \ReflectionMethod($translations, 'create');
		$reflectionMethod->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($translations, '60', 65, 'c6'));
		$this->assertCount(5, $translations->getCollection());
		$this->assertNull($translations->getCollection()->get(6));
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetchSuccess()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['load', 'methodGet'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$translations->expects($this->never())->method('methodGet');

		$this->assertEquals([
			'de-DE' => [
				'c1key' => 'c1',
				'c2key' => 'c2',
				'c3key' => 'c3',
				'c4key' => 'c4',
			],
			'ru-RU' => [
				'c5key' => 'c5',
			]
		], $translations->fetch());
	}

	/**
	 * @covers ::store
	 */
	public function testStoreSuccessCreate()
	{
		$this->markTestIncomplete();
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodPost'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodPost')->with('translations/store', [
			'locale' => 'de-DE',
			'key' => 'a/b/c/de.de',
			'content' => 'mäh',
		])->willReturn([]);

		$this->assertTrue($translations->store('de-DE', 'a/b/c/de.de', 'mäh'));
	}

	/**
	 * @covers ::store
	 */
	public function testStoreSuccessUpdate()
	{
		$this->markTestIncomplete();
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodPost'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodPost')->with('translations/store', [
			'locale' => 'de-DE',
			'key' => 'a/b/c/de.de',
			'content' => 'mäh',
		])->willReturn([]);

		$this->assertTrue($translations->store('de-DE', 'a/b/c/de.de', 'mäh'));
	}

	/**
	 * @covers ::store
	 */
	public function testStoreFailedByLocale()
	{
		$this->markTestIncomplete();
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodPost'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodPost')->with('translations/store', [
			'locale' => 'de-DE',
			'key' => 'a/b/c/de.de',
			'content' => 'mäh',
		])->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($translations->store('de-DE', 'a/b/c/de.de', 'mäh'));
	}

	/**
	 * @covers ::store
	 */
	public function testStoreFailedByKey()
	{
		$this->markTestIncomplete();
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodPost'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodPost')->with('translations/store', [
			'locale' => 'de-DE',
			'key' => 'a/b/c/de.de',
			'content' => 'mäh',
		])->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($translations->store('de-DE', 'a/b/c/de.de', 'mäh'));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateSuccess()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['load', 'methodPatch'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$translations->expects($this->once())->method('methodPatch')->with(Translations::URL_API . 5, [
			'content' => 'narf'
		])->willReturn(
			['id' => 5, 'content' => 'narf', 'locale' => ['id' => 50, 'code' => 'ru-RU'], 'key' => ['id' => 55, 'name' => 'c5key']]
		);

		$reflectionMethod = new \ReflectionMethod($translations, 'update');
		$reflectionMethod->setAccessible(true);

		$this->assertTrue($reflectionMethod->invoke($translations, 5, 'narf'));
		$this->assertCount(5, $translations->getCollection());
		$this->assertEquals(
			['id' => 5, 'content' => 'narf', 'locale' => ['id' => 50, 'code' => 'ru-RU'], 'key' => ['id' => 55, 'name' => 'c5key']]
		, $translations->getCollection()->get(5));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateFailed()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['load', 'methodPatch'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$translations->expects($this->once())->method('methodPatch')->with(Translations::URL_API . 5, [
			'content' => 'narf'
		])->willThrowException(new \DasRed\PhraseApp\Exception());

		$reflectionMethod = new \ReflectionMethod($translations, 'update');
		$reflectionMethod->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($translations, 5, 'narf'));
		$this->assertCount(5, $translations->getCollection());
		$this->assertEquals(
			['id' => 5, 'content' => 'c5', 'locale' => ['id' => 50, 'code' => 'ru-RU'], 'key' => ['id' => 55, 'name' => 'c5key']]
		, $translations->getCollection()->get(5));

	}

	/**
	 * @covers ::getIdKey
	 */
	public function testGetIdKey()
	{
		$translations = new Translations($this->config);

		$reflectionMethod = new \ReflectionMethod($translations, 'getIdKey');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('id', $reflectionMethod->invoke($translations));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadSuccess()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodGet')->with(Translations::URL_API)->willReturn([['id' => 'a'], ['id' => 'b']]);

		$reflectionMethod = new \ReflectionMethod($translations, 'load');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([['id' => 'a'], ['id' => 'b']], $reflectionMethod->invoke($translations));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadFailed()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodGet')->with(Translations::URL_API)->willThrowException(new \DasRed\PhraseApp\Exception());

		$reflectionMethod = new \ReflectionMethod($translations, 'load');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([], $reflectionMethod->invoke($translations));
	}
}
