<?php
namespace DasRedTest\PhraseApp\Synchronize\Files\Exception;

use DasRed\PhraseApp\Synchronize\Files\Exception\InvalidPath;
use DasRed\PhraseApp\Synchronize\Files\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Files\Exception\InvalidPath
 */
class InvalidPathTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new InvalidPath('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$this->markTestIncomplete();
		$exception = new InvalidPath('nuff/');

		$this->assertEquals('The path "nuff/" is invalid.', $exception->getMessage());
	}

}
