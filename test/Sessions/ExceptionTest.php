<?php
namespace DasRedTest\PhraseApp\Sessions;

use DasRed\PhraseApp\Sessions\Exception;
use DasRed\PhraseApp\Exception as ExceptionBase;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Sessions\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new Exception();
		$this->assertTrue($exception instanceof ExceptionBase);
	}
}