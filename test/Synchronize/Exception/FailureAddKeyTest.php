<?php
namespace DasRedTest\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception\FailureAddKey;
use DasRed\PhraseApp\Synchronize\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Exception\FailureAddKey
 */
class FailureAddKeyTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new FailureAddKey('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new FailureAddKey('nuff');

		$this->assertEquals('Can not create translation key "nuff".', $exception->getMessage());
	}
}
