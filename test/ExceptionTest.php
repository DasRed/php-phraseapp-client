<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Exception;
use Exception as ExceptionBase;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new Exception();
		$this->assertTrue($exception instanceof ExceptionBase);
	}
}
