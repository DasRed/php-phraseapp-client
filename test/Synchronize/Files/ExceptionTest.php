<?php
namespace DasRedTest\PhraseApp\Synchronize\Files;

use DasRed\PhraseApp\Synchronize\Files\Exception;
use DasRed\PhraseApp\Synchronize\Exception as ExceptionBase;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Files\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new Exception();
		$this->assertTrue($exception instanceof ExceptionBase);
	}
}
