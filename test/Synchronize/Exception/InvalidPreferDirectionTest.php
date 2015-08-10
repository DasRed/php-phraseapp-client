<?php
namespace DasRedTest\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception\InvalidPreferDirection;
use DasRed\PhraseApp\Synchronize\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Exception\InvalidPreferDirection
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

		$this->assertEquals('Unknown prefer direction "nuff" for synchronize', $exception->getMessage());
	}
}
