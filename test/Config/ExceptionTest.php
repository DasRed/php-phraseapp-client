<?php
namespace DasRedTest\PhraseApp\Config;

use DasRed\PhraseApp\Config\Exception;
use DasRed\PhraseApp\Exception as ExceptionBase;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Config\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new Exception();
		$this->assertTrue($exception instanceof ExceptionBase);
	}
}
