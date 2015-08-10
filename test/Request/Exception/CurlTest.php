<?php
namespace DasRedTest\PhraseApp\Request\Exception;

use DasRed\PhraseApp\Request\Exception\Curl;
use DasRed\PhraseApp\Request\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Request\Exception\Curl
 */
class CurlTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new Curl();
		$this->assertTrue($exception instanceof Exception);
	}
}
