<?php
namespace DasRedTest\PhraseApp\Request\Exception;

use DasRed\PhraseApp\Request\Exception\Json;
use DasRed\PhraseApp\Request\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Request\Exception\Json
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new Json('a');
		$this->assertTrue($exception instanceof Exception);
	}
}
