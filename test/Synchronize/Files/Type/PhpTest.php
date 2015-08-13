<?php
namespace DasRedTest\PhraseApp\Synchronize\Files\Type;

use DasRed\PhraseApp\Synchronize\Files\Type\Php;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Files\Type\Php
 */
class PhpTest extends \PHPUnit_Framework_TestCase
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
	 * @covers ::getDescriptionForKey
	 */
	public function testGetDescriptionForKey()
	{
		$php = new Php(__DIR__);

		$this->assertSame('This is a translation key from the file "abc.php".', $php->getDescriptionForKey('abc.def'));
	}

	/**
	 * @covers ::getFileExtension
	 */
	public function testGetFileExtension()
	{
		$php = new Php(__DIR__);

		$reflectionMethod = new \ReflectionMethod($php, 'getFileExtension');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('php', $reflectionMethod->invoke($php));
	}

	public function dataProviderReadFile()
	{
		return [
			['de-DE/other.php', '', ['a' => 'cother', 'a.b.c' => 'gkjreqwbgukie', 'bb' => '[b]bbcode[/b] bb']],
			['de-DE/other.php', 'DE', ['DE.a' => 'cother', 'DE.a.b.c' => 'gkjreqwbgukie', 'DE.bb' => '[b]bbcode[/b] bb']],
		];
	}

	/**
	 * @covers ::readFile
	 * @dataProvider dataProviderReadFile
	 */
	public function testReadFile($file, $keyPrefix, $expected)
	{
		$php = new Php(__DIR__ . '/translations/php');

		$reflectionMethod = new \ReflectionMethod($php, 'readFile');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals($expected, $reflectionMethod->invoke($php, __DIR__ . '/translations/php/' . $file, $keyPrefix));
	}

	public function dataProviderWriteFile()
	{
		return [
			['nuff.php', ['bb' => '[b]bbcode[/b] bb', 'a.b.c' => 'gkjreqw\'bgukie', 'd' => '32q1r'], "<?php\n\nreturn [\n\t'a.b.c' => 'gkjreqw\\'bgukie',\n\t'bb' => '[b]bbcode[/b] bb',\n\t'd' => '32q1r',\n];\n"]
		];
	}

	/**
	 * @covers ::writeFile
	 * @dataProvider dataProviderWriteFile
	 */
	public function testWriteFile($file, $translations, $content)
	{
		$file = $this->path . $file;

		$php = new Php($this->path);

		$reflectionMethod = new \ReflectionMethod($php, 'writeFile');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($php, $reflectionMethod->invoke($php, $file, $translations));

		$this->assertFileExists($file);
		$this->assertSame($content, file_get_contents($file));
	}
}
