<?php
namespace DasRedTest\PhraseApp\Synchronize\Files;

use DasRed\PhraseApp\Synchronize\Files\TypeAbstract;
use DasRed\PhraseApp\Synchronize\Files\Exception\InvalidPath;
use DasRed\PhraseApp\Synchronize;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Files\TypeAbstract
 */
class TypeAbstractTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods([])->setConstructorArgs([__DIR__])->getMockForAbstractClass();
		$this->assertSame(trim(str_replace('\\', '/', __DIR__), '/') . '/', $type->getPath());
		$this->assertEquals([], $type->getExcludeNames());

		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods([])->setConstructorArgs([__DIR__, ['A']])->getMockForAbstractClass();
		$this->assertSame(trim(str_replace('\\', '/', __DIR__), '/') . '/', $type->getPath());
		$this->assertEquals(['A'], $type->getExcludeNames());
	}

	public function dataProviderCollectFiles()
	{
		$path = str_replace('\\', '/', __DIR__ . '/translations/typeAbstract/');
		return [
			['php', [], '/^([a-zA-Z_0-9]+)\.php$/', $path, [
				['file' => $path . 'de-DE/other.php', 'locale' => 'de-DE', 'name' => 'other'],
				['file' => $path . 'de-DE/test.php', 'locale' => 'de-DE', 'name' => 'test'],
				['file' => $path . 'ru-RU/test/a/nuff/module.php', 'locale' => 'ru-RU', 'name' => 'test/a/nuff/module'],
			]],

			['php', ['module'], '/^([a-zA-Z_0-9]+)\.php$/', $path, [
				['file' => $path . 'de-DE/other.php', 'locale' => 'de-DE', 'name' => 'other'],
				['file' => $path . 'de-DE/test.php', 'locale' => 'de-DE', 'name' => 'test'],
			]],
			['php', ['test/a/nuff/module'], '/^([a-zA-Z_0-9]+)\.php$/', $path, [
				['file' => $path . 'de-DE/other.php', 'locale' => 'de-DE', 'name' => 'other'],
				['file' => $path . 'de-DE/test.php', 'locale' => 'de-DE', 'name' => 'test'],
			]],
		];
	}

	/**
	 * @covers ::collectFiles
	 * @dataProvider dataProviderCollectFiles
	 */
	public function testCollectFiles($fileExtension, $excludeNames, $fileStyle, $path, $expected)
	{
		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods(['getFileExtension', 'getExcludeNames', 'getFileStyle'])->setConstructorArgs([$path])->getMockForAbstractClass();
		$type->expects($this->any())->method('getFileExtension')->with()->willReturn($fileExtension);
		$type->expects($this->any())->method('getExcludeNames')->with()->willReturn($excludeNames);
		$type->expects($this->any())->method('getFileStyle')->with()->willReturn($fileStyle);

		$reflectionMethod = new \ReflectionMethod($type, 'collectFiles');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals($expected, $reflectionMethod->invoke($type));
	}

	/**
	 * @covers ::getExcludeNames
	 * @covers ::setExcludeNames
	 */
	public function testGetSetExcludeNames()
	{
		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods([])->setConstructorArgs([__DIR__])->getMockForAbstractClass();

		$reflectionMethod = new \ReflectionMethod($type, 'setExcludeNames');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([], $type->getExcludeNames());
		$this->assertSame($type, $reflectionMethod->invoke($type, ['A']));
		$this->assertEquals(['A'], $type->getExcludeNames());
	}

	public function dataProviderGetKeyInformation()
	{
		return [
			['abcdef', 'php', ['file' => '', 'filePart' => '', 'key' => 'abcdef', 'fileKey' => '']],
			['abc.def', 'php', ['file' => 'abc.php', 'filePart' => 'abc', 'key' => 'def', 'fileKey' => 'abc.def']],
			['test/a/nuff/module.def', 'php', ['file' => 'test/a/nuff/module.php', 'filePart' => 'test/a/nuff/module', 'key' => 'def', 'fileKey' => 'test/a/nuff/module.def']],
		];
	}

	/**
	 * @covers ::getKeyInformation
	 * @dataProvider dataProviderGetKeyInformation
	 */
	public function testGetKeyInformation($key, $fileExtension, $expected)
	{
		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods(['getFileExtension'])->setConstructorArgs([__DIR__])->getMockForAbstractClass();
		$type->expects($this->any())->method('getFileExtension')->with()->willReturn($fileExtension);

		$reflectionMethod = new \ReflectionMethod($type, 'getKeyInformation');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals($expected, $reflectionMethod->invoke($type, $key));
	}

	/**
	 * @covers ::getPath
	 * @covers ::setPath
	 */
	public function testGetSetPath()
	{
		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods([])->setConstructorArgs([__DIR__])->getMockForAbstractClass();

		$reflectionMethod = new \ReflectionMethod($type, 'setPath');
		$reflectionMethod->setAccessible(true);

		$this->assertSame(trim(str_replace('\\', '/', __DIR__), '/') . '/', $type->getPath());
		$this->assertSame($type, $reflectionMethod->invoke($type, __DIR__ . '/translations'));
		$this->assertSame(trim(str_replace('\\', '/', __DIR__ . '/translations'), '/') . '/', $type->getPath());
	}

	/**
	 * @covers ::setPath
	 */
	public function testSetPathFailed()
	{
		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods([])->setConstructorArgs([__DIR__])->getMockForAbstractClass();

		$reflectionMethod = new \ReflectionMethod($type, 'setPath');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(InvalidPath::class);
		$reflectionMethod->invoke($type, '/NUFF&.:_/');
	}

	public function dataProviderPrepare()
	{
		return [
			['', ['rofl' => 'lol', 'narf' => 'nuff'], ['rofl' => 'lol', 'narf' => 'nuff']],
			['haha', ['rofl' => 'lol', 'narf' => 'nuff'], ['haha.rofl' => 'lol', 'haha.narf' => 'nuff']],
		];
	}

	/**
	 * @covers ::prepare
	 * @dataProvider dataProviderPrepare
	 */
	public function testPrepare($keyPrefix, $translations, $expected)
	{
		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods([])->setConstructorArgs([__DIR__])->getMockForAbstractClass();

		$reflectionMethod = new \ReflectionMethod($type, 'prepare');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals($expected, $reflectionMethod->invoke($type, $keyPrefix, $translations));
	}

	/**
	 * @covers ::read
	 */
	public function testRead()
	{
		$files = [
			['file' => 'nuff.php', 'locale' => 'de-DE', 'name' => 'nuff'],
			['file' => 'narf.php', 'locale' => 'ru-RU', 'name' => 'narf'],
		];

		$synchronize = $this->getMockBuilder(Synchronize::class)->setMethods(['addTranslations'])->disableOriginalConstructor()->getMock();
		$synchronize->expects($this->exactly(2))->method('addTranslations')->withConsecutive([
			'de-DE', ['narf' => 'nuff', 'rofl' => 'lol']
		], [
			'ru-RU', ['haha.narf' => 'nuff', 'haha.rofl' => 'lol']
		])->willReturnSelf();

		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods(['collectFiles', 'readFiles'])->setConstructorArgs([__DIR__])->getMockForAbstractClass();
		$type->expects($this->once())->method('collectFiles')->with()->willReturn($files);
		$type->expects($this->exactly(2))->method('readFile')->withConsecutive([
			'nuff.php', 'nuff'
		], [
			'narf.php', 'narf'
		])->willReturnOnConsecutiveCalls([
			'rofl' => 'lol', 'narf' => 'nuff'
		], [
			'haha.rofl' => 'lol', 'haha.narf' => 'nuff'
		]);

		$this->assertTrue($type->read($synchronize));
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		$this->markTestIncomplete();
	}
}
