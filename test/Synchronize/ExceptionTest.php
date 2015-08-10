<?php
namespace DasRedTest\PhraseApp\Synchronize;

use DasRed\PhraseApp\Synchronize\Exception;
use DasRed\PhraseApp\Exception as ExceptionBase;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new Exception();
		$this->assertTrue($exception instanceof ExceptionBase);
	}
}
