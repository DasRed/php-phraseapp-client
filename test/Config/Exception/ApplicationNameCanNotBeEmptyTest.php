<?php
namespace DasRedTest\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception\ApplicationNameCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Config\Exception\ApplicationNameCanNotBeEmpty
 */
class ApplicationNameCanNotBeEmptyTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new ApplicationNameCanNotBeEmpty();
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new ApplicationNameCanNotBeEmpty();

		$this->assertEquals('The application name can not be empty.', $exception->getMessage());
	}
}
