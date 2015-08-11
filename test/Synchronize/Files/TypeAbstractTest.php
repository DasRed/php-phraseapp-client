<?php
namespace DasRedTest\PhraseApp\Synchronize\Files;

use DasRed\PhraseApp\Synchronize\Files\TypeAbstract;
use DasRed\PhraseApp\Synchronize\Files\Exception\InvalidPath;
use DasRed\PhraseApp\Synchronize;
use DasRed\PhraseApp\Synchronize\Files\Exception\FailureCreateLocalePath;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Files\TypeAbstract
 */
class TypeAbstractTest extends \PHPUnit_Framework_TestCase
{
	protected $path;

	public function setUp()
	{
		parent::setUp();

		$this->path = rtrim(str_replace('\\', '/', __DIR__ . '/translations/temp-test')) . '/';

		if (file_exists($this->path) === false)
		{
			mkdir($this->path, 0777, true);
		}
	}

	public function tearDown()
	{
		parent::tearDown();

		if (file_exists($this->path) === true)
		{
			$iterator = new \RecursiveDirectoryIterator($this->path);
			foreach (new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST) as $file)
			{
				if ($file->isDir() === true)
				{
					if (in_array($file->getBasename(), ['.', '..']) === false)
					{
						rmdir($file->getPathname());
					}
				}
				else
				{
					unlink($file->getPathname());
				}
			}
			rmdir($this->path);
		}
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods([])->setConstructorArgs([__DIR__])->getMockForAbstractClass();
		$this->assertSame(rtrim(str_replace('\\', '/', __DIR__), '/') . '/', $type->getPath());
		$this->assertEquals([], $type->getExcludeNames());

		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods([])->setConstructorArgs([__DIR__, ['A']])->getMockForAbstractClass();
		$this->assertSame(rtrim(str_replace('\\', '/', __DIR__), '/') . '/', $type->getPath());
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

			['php', ['es'], '/^([a-zA-Z_0-9]+)\.php$/', $path, [
				['file' => $path . 'de-DE/other.php', 'locale' => 'de-DE', 'name' => 'other'],
			]],

			['php', ['(a/nu)|(es)'], '/^([a-zA-Z_0-9]+)\.php$/', $path, [
				['file' => $path . 'de-DE/other.php', 'locale' => 'de-DE', 'name' => 'other'],
			]],

			['php', ['^test/'], '/^([a-zA-Z_0-9]+)\.php$/', $path, [
				['file' => $path . 'de-DE/other.php', 'locale' => 'de-DE', 'name' => 'other'],
				['file' => $path . 'de-DE/test.php', 'locale' => 'de-DE', 'name' => 'test'],
			]],

			['php', ['(er|st|ule)$'], '/^([a-zA-Z_0-9]+)\.php$/', $path, []],
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

		$this->assertSame(rtrim(str_replace('\\', '/', __DIR__), '/') . '/', $type->getPath());
		$this->assertSame($type, $reflectionMethod->invoke($type, __DIR__ . '/translations'));
		$this->assertSame(rtrim(str_replace('\\', '/', __DIR__ . '/translations'), '/') . '/', $type->getPath());
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
		$synchronize->expects($this->exactly(2))->method('addTranslations')->withConsecutive(
			['de-DE', ['narf' => 'nuff', 'rofl' => 'lol']],
			['ru-RU', ['haha.narf' => 'nuff', 'haha.rofl' => 'lol']]
		)->willReturnSelf();

		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods(['collectFiles', 'readFiles'])->setConstructorArgs([__DIR__])->getMockForAbstractClass();
		$type->expects($this->once())->method('collectFiles')->with()->willReturn($files);
		$type->expects($this->exactly(2))->method('readFile')->withConsecutive(
			['nuff.php', 'nuff'],
			['narf.php', 'narf']
		)->willReturnOnConsecutiveCalls(
			['rofl' => 'lol', 'narf' => 'nuff'],
			['haha.rofl' => 'lol', 'haha.narf' => 'nuff']
		);

		$this->assertTrue($type->read($synchronize));
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		$synchronize = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations'])->disableOriginalConstructor()->getMock();
		$synchronize->expects($this->once())->method('getTranslations')->with()->willReturn([
			'de-DE' => ['haha.narf' => 'nuff', 'haha.rofl' => 'lol', 'muh.nuff' => 'roflcopter', 'abc' => 'def'],
			'ru-RU' => ['test/a/nuff/haha.narf' => 'nuff', 'test/a/nuff/haha.rofl' => 'lol', 'test/a/nuff/muh.nuff' => 'roflcopter'],
		]);

		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods(['writeFile', 'getFileExtension'])->setConstructorArgs([$this->path])->getMockForAbstractClass();
		$type->expects($this->any())->method('getFileExtension')->with()->willReturn('php');
		$type->expects($this->exactly(4))->method('writeFile')->withConsecutive(
			[$this->path . 'de-DE/haha.php', ['narf' => 'nuff', 'rofl' => 'lol']],
			[$this->path . 'de-DE/muh.php', ['nuff' => 'roflcopter']],
			[$this->path . 'ru-RU/test/a/nuff/haha.php', ['narf' => 'nuff', 'rofl' => 'lol']],
			[$this->path . 'ru-RU/test/a/nuff/muh.php', ['nuff' => 'roflcopter']]
		)->willReturnSelf();

		$this->assertTrue($type->write($synchronize));

		$this->assertFileExists($this->path . 'de-DE');
		$this->assertFileExists($this->path . 'ru-RU/test/a/nuff');
	}

	/**
	 * @covers ::write
	 */
	public function testWriteFailed()
	{
		file_put_contents($this->path . 'de-DE', ' ');

		$synchronize = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations'])->disableOriginalConstructor()->getMock();
		$synchronize->expects($this->once())->method('getTranslations')->with()->willReturn([
			'de-DE' => ['a/b/c/haha.narf' => 'nuff'],
		]);

		$type = $this->getMockBuilder(TypeAbstract::class)->setMethods(['writeFile', 'getFileExtension'])->setConstructorArgs([$this->path])->getMockForAbstractClass();
		$type->expects($this->any())->method('getFileExtension')->willReturn('php');
		$type->expects($this->any())->method('writeFile')->willReturnSelf();

		$this->setExpectedException(FailureCreateLocalePath::class);
		$type->write($synchronize);
	}
}
