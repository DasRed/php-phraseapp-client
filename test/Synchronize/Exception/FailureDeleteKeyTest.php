<?php
namespace DasRedTest\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception\FailureDeleteKey;
use DasRed\PhraseApp\Synchronize\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Exception\FailureDeleteKey
 */
class FailureDeleteKeyTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new FailureDeleteKey('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new FailureDeleteKey('nuff');

		$this->assertEquals('Can not delete translation key "nuff".', $exception->getMessage());
	}
}
