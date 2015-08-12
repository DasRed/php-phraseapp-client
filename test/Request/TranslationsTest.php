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

		$this->config = new Config('b', 'de', 'appName', 'a');
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->config = null;
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetch()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['fetchAll', 'fetchForLocale'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('fetchAll')->with()->willReturn(['A']);
		$translations->expects($this->never())->method('fetchForLocale');

		$this->assertSame(['A'], $translations->fetch());

		$translations = $this->getMockBuilder(Translations::class)->setMethods(['fetchAll', 'fetchForLocale'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->never())->method('fetchAll');
		$translations->expects($this->once())->method('fetchForLocale')->with()->willReturn(['A']);

		$this->assertSame(['A'], $translations->fetch('de-DE'));
	}

	/**
	 * @covers ::fetchAll
	 */
	public function testFetchAllSuccess()
	{
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

		$reflectionMethod = new \ReflectionMethod($translations, 'fetchAll');
		$reflectionMethod->setAccessible(true);

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
		], $reflectionMethod->invoke($translations));
	}

	/**
	 * @covers ::fetchAll
	 */
	public function testFetchAllFailed()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translations/')->willThrowException(new \DasRed\PhraseApp\Exception());

		$reflectionMethod = new \ReflectionMethod($translations, 'fetchAll');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([], $reflectionMethod->invoke($translations));
	}

	/**
	 * @covers ::fetchForLocale
	 */
	public function testFetchForLocaleSuccess()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translations/', ['locale_name' => 'de-DE'])->willReturn([
			['translation_key' => ['name' => 'abc'], 'content' => 'def'],
			['translation_key' => ['name' => 'nuff'], 'content' => 'narf'],
			['translation_key' => ['name' => 'lol'], 'content' => 'rofl'],
		]);

		$reflectionMethod = new \ReflectionMethod($translations, 'fetchForLocale');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([
			'abc' => 'def',
			'nuff' => 'narf',
			'lol' => 'rofl',
		], $reflectionMethod->invoke($translations, 'de-DE'));
	}

	/**
	 * @covers ::fetchForLocale
	 */
	public function testFetchForLocaleFailed()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translations/', ['locale_name' => 'de-DE'])->willThrowException(new \DasRed\PhraseApp\Exception());

		$reflectionMethod = new \ReflectionMethod($translations, 'fetchForLocale');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([], $reflectionMethod->invoke($translations, 'de-DE'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParse()
	{
		$translations = new Translations($this->config);

		$reflectionMethod = new \ReflectionMethod($translations, 'parse');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([
			'abc' => 'def',
			'nuff' => 'narf',
			'lol' => 'rofl',
		], $reflectionMethod->invoke($translations, [
			['translation_key' => ['name' => 'abc'], 'content' => 'def'],
			['translation_key' => ['name' => 'nuff'], 'content' => 'narf'],
			['translation_key' => ['name' => 'lol'], 'content' => 'rofl'],
		]));
	}

	/**
	 * @covers ::store
	 */
	public function testStoreSuccess()
	{
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
	public function testStoreFailed()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['methodPost'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('methodPost')->with('translations/store', [
			'locale' => 'de-DE',
			'key' => 'a/b/c/de.de',
			'content' => 'mäh',
		])->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($translations->store('de-DE', 'a/b/c/de.de', 'mäh'));
	}
}
