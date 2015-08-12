<?php
namespace DasRedTest\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception\BaseUrlCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Config\Exception\BaseUrlCanNotBeEmpty
 */
class BaseUrlCanNotBeEmptyTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new BaseUrlCanNotBeEmpty();
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new BaseUrlCanNotBeEmpty();

		$this->assertEquals('The base url can not be empty.', $exception->getMessage());
	}
}
