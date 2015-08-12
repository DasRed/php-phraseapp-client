<?php
namespace DasRedTest\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception\AccessTokenCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Config\Exception\AccessTokenCanNotBeEmpty
 */
class AccessTokenCanNotBeEmptyTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new AccessTokenCanNotBeEmpty();
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new AccessTokenCanNotBeEmpty();

		$this->assertEquals('The access token can not be empty.', $exception->getMessage());
	}
}
