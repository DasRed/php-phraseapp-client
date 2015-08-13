<?php
namespace DasRedTest\PhraseApp\Command\Exception;

use DasRed\PhraseApp\Command\Exception\InvalidArguments;
use DasRed\PhraseApp\Command\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Exception\InvalidArguments
 */
class InvalidArgumentsTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new InvalidArguments();
		$this->assertTrue($exception instanceof Exception);
	}
}
