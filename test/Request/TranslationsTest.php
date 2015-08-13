<?php
namespace DasRedTest\PhraseApp\Request;

use DasRed\PhraseApp\Request\Translations;
use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\Request\Locales;
use DasRed\PhraseApp\Request\Keys;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request\Translations
 */
class TranslationsTest extends \PHPUnit_Framework_TestCase
{
	protected $config;
	protected $locales;
	protected $keys;

	protected $loadWithData = [
		1 => ['id' => 1, 'content' => 'c1', 'locale' => ['id' => 10, 'code' => 'de-DE'], 'key' => ['id' => 15, 'name' => 'c1key']],
		2 => ['id' => 2, 'content' => 'c2', 'locale' => ['id' => 10, 'code' => 'de-DE'], 'key' => ['id' => 25, 'name' => 'c2key']],
		3 => ['id' => 3, 'content' => 'c3', 'locale' => ['id' => 10, 'code' => 'de-DE'], 'key' => ['id' => 35, 'name' => 'c3key']],
		4 => ['id' => 4, 'content' => 'c4', 'locale' => ['id' => 10, 'code' => 'de-DE'], 'key' => ['id' => 45, 'name' => 'c4key']],
		5 => ['id' => 5, 'content' => 'c5', 'locale' => ['id' => 50, 'code' => 'ru-RU'], 'key' => ['id' => 55, 'name' => 'c5key']],
	];

	protected $localesData = [
		'de-DE' => ['code' => 'de-DE', 'id' => 10],
		'ru-RU' => ['code' => 'ru-RU', 'id' => 50],
	];

	protected $keysData = [
		'c1key' => ['name' => 'c1key', 'id' => 15],
		'c2key' => ['name' => 'c2key', 'id' => 25],
		'c3key' => ['name' => 'c3key', 'id' => 35],
		'c4key' => ['name' => 'c4key', 'id' => 45],
		'c5key' => ['name' => 'c5key', 'id' => 55],
		'c6key' => ['name' => 'c6key', 'id' => 65],
	];

	public function setUp()
	{
		parent::setUp();

		$this->config = new Config('pp', 'b', 'de');
		$this->config->setApplicationName('appName')->setBaseUrl('a');

		$this->locales = new Locales($this->config);
		$this->locales->getCollection()->combine($this->localesData);
		$this->keys = new Keys($this->config);
		$this->keys->getCollection()->combine($this->keysData);
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->config = null;
		$this->locales = null;
		$this->keys = null;
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
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['load', 'getPhraseAppLocales', 'getPhraseAppKeys', 'create', 'update'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$translations->expects($this->exactly(2))->method('getPhraseAppLocales')->with()->willReturn($this->locales);
		$translations->expects($this->exactly(2))->method('getPhraseAppKeys')->with()->willReturn($this->keys);
		$translations->expects($this->exactly(2))->method('create')->withConsecutive(
			[10, 65, 'nuff'],
			[50, 15, 'narf']
		)->willReturn(true);
		$translations->expects($this->never())->method('update');

		$this->assertTrue($translations->store('de-DE', 'c6key', 'nuff'));
		$this->assertTrue($translations->store('ru-RU', 'c1key', 'narf'));
	}

	/**
	 * @covers ::store
	 */
	public function testStoreSuccessUpdate()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['load', 'getPhraseAppLocales', 'getPhraseAppKeys', 'create', 'update'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$translations->expects($this->exactly(2))->method('getPhraseAppLocales')->with()->willReturn($this->locales);
		$translations->expects($this->exactly(2))->method('getPhraseAppKeys')->with()->willReturn($this->keys);
		$translations->expects($this->never())->method('create');
		$translations->expects($this->exactly(2))->method('update')->withConsecutive(
			[1, 'nuff'],
			[5, 'narf']
		)->willReturn(true);

		$this->assertTrue($translations->store('de-DE', 'c1key', 'nuff'));
		$this->assertTrue($translations->store('ru-RU', 'c5key', 'narf'));
	}

	/**
	 * @covers ::store
	 */
	public function testStoreFailedByLocale()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['load', 'getPhraseAppLocales', 'getPhraseAppKeys', 'create', 'update'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->never())->method('load');
		$translations->expects($this->once())->method('getPhraseAppLocales')->with()->willReturn($this->locales);
		$translations->expects($this->never())->method('getPhraseAppKeys');
		$translations->expects($this->never())->method('create');
		$translations->expects($this->never())->method('update');

		$this->assertFalse($translations->store('de-RU-Hans', 'c1key', 'nuff'));
	}

	/**
	 * @covers ::store
	 */
	public function testStoreFailedByKey()
	{
		$translations = $this->getMockBuilder(Translations::class)->setMethods(['load', 'getPhraseAppLocales', 'getPhraseAppKeys', 'create', 'update'])->setConstructorArgs([$this->config])->getMock();
		$translations->expects($this->never())->method('load');
		$translations->expects($this->once())->method('getPhraseAppLocales')->with()->willReturn($this->locales);
		$translations->expects($this->once())->method('getPhraseAppKeys')->with()->willReturn($this->keys);
		$translations->expects($this->never())->method('create');
		$translations->expects($this->never())->method('update');

		$this->assertFalse($translations->store('de-DE', 'snuff', 'nuff'));
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
	 * @covers ::getUrlApi
	 */
	public function testGetUrlApi()
	{
		$translations = new Translations($this->config);

		$reflectionMethod = new \ReflectionMethod($translations, 'getUrlApi');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(Translations::URL_API, $reflectionMethod->invoke($translations));
	}
}
