<?php
namespace DasRedTest\PhraseApp\Command\Exception;

use DasRed\PhraseApp\Command\Exception\InvalidCommand;
use DasRed\PhraseApp\Command\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Exception\InvalidCommand
 */
class InvalidCommandTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new InvalidCommand('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function test__construct()
	{
		$exception = new InvalidCommand('a');
		$this->assertSame('Command "a" not found.', $exception->getMessage());
	}
}
