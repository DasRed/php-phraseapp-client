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
	 * @covers ::fetch
	 */
	public function testFetchSuccess()
	{
		$this->markTestIncomplete();
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translations/')->willReturn([
			'en-UK' => [
				['translation_key' => ['name' => 'nuff'], 'content' => 'narf'],
				['translation_key' => ['name' => 'lol'], 'content' => 'rofl'],
				['translation_key' => ['name' => 'muh'], 'content' => 'mäh'],
			],
			'de-DE' => [
				['translation_key' => ['name' => 'abc'], 'content' => 'def'],
				['translation_key' => ['name' => 'nuff'], 'content' => 'narf'],
				['translation_key' => ['name' => 'lol'], 'content' => 'rofl'],
			],
		]);

		$this->assertEquals([
			'en-UK' => [
				'nuff' => 'narf',
				'lol' => 'rofl',
				'muh' => 'mäh',
			],
			'de-DE' => [
				'abc' => 'def',
				'nuff' => 'narf',
				'lol' => 'rofl',
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


	public function testCreate()
	{
		$this->markTestIncomplete();
	}

	public function testUpdate()
	{
		$this->markTestIncomplete();
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
