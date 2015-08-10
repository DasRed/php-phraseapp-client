<?php
namespace DasRedTest\PhraseApp\Request;

use DasRed\PhraseApp\Request\Exception;
use DasRed\PhraseApp\Exception as ExceptionBase;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Request\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new Exception();
		$this->assertTrue($exception instanceof ExceptionBase);
	}
}
