<?php
namespace DasRedTest\PhraseApp\Sessions\Exception;

use DasRed\PhraseApp\Sessions\Exception\LoginFailed;
use DasRed\PhraseApp\Sessions\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Exception\LoginFailed
 */
class LoginFailedTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new LoginFailed('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new LoginFailed('nuff');

		$this->assertEquals('Can not login with credentials "nuff".', $exception->getMessage());
	}
}
