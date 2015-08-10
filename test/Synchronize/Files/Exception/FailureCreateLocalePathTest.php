<?php
namespace DasRedTest\PhraseApp\Synchronize\Files\Exception;

use DasRed\PhraseApp\Synchronize\Files\Exception\FailureCreateLocalePath;
use DasRed\PhraseApp\Synchronize\Files\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Files\Exception\FailureCreateLocalePath
 */
class FailureCreateLocalePathTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new FailureCreateLocalePath('a', 'b');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$this->markTestIncomplete();
		$exception = new FailureCreateLocalePath('nuff/', 'lol');

		$this->assertEquals('Locale path "nuff/lol" can not be created.', $exception->getMessage());
	}

}
