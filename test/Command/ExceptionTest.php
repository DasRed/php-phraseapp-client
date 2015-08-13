<?php
namespace DasRedTest\PhraseApp\Command;

use DasRed\PhraseApp\Command\Exception;
use DasRed\PhraseApp\Exception as ExceptionBase;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new Exception();
		$this->assertTrue($exception instanceof ExceptionBase);
	}
}
