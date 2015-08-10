<?php
namespace DasRedTest\PhraseApp\Request\Exception;

use DasRed\PhraseApp\Request\Exception\HttpStatus;
use DasRed\PhraseApp\Request\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Request\Exception\HttpStatus
 */
class HttpStatusTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new HttpStatus('a');
		$this->assertTrue($exception instanceof Exception);
	}
}
