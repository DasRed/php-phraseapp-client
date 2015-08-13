<?php
namespace DasRedTest\PhraseApp\Command\Exception;

use DasRed\PhraseApp\Command\Exception\InvalidCommandOperation;
use DasRed\PhraseApp\Command\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Exception\InvalidCommandOperation
 */
class InvalidCommandOperationTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new InvalidCommandOperation('a', 'b');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function test__construct()
	{
		$exception = new InvalidCommandOperation('a', 'b');
		$this->assertSame('Operation "b" not found for command "a".', $exception->getMessage());
	}
}
