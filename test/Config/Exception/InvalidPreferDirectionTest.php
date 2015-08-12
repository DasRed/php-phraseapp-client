<?php
namespace DasRedTest\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception\InvalidPreferDirection;
use DasRed\PhraseApp\Config\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Config\Exception\InvalidPreferDirection
 */
class InvalidPreferDirectionTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new InvalidPreferDirection('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new InvalidPreferDirection('nuff');

		$this->assertEquals('Unknown prefer direction "nuff".', $exception->getMessage());
	}
}
