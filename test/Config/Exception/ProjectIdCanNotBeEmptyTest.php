<?php
namespace DasRedTest\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception\ProjectIdCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Config\Exception\ProjectIdCanNotBeEmpty
 */
class ProjectIdCanNotBeEmptyTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new ProjectIdCanNotBeEmpty();
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new ProjectIdCanNotBeEmpty();

		$this->assertEquals('The project id can not be empty.', $exception->getMessage());
	}
}
