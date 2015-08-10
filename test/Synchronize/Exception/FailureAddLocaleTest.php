<?php
namespace DasRedTest\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception\FailureAddLocale;
use DasRed\PhraseApp\Synchronize\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Exception\FailureAddLocale
 */
class FailureAddLocaleTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new FailureAddLocale('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new FailureAddLocale('nuff');

		$this->assertEquals('Can not create locale "nuff".', $exception->getMessage());
	}
}
