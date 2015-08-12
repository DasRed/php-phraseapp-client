<?php
namespace DasRedTest\PhraseApp\Config\Exception;

use DasRed\PhraseApp\Config\Exception\LocaleDefaultCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Config\Exception\LocaleDefaultCanNotBeEmpty
 */
class LocaleDefaultCanNotBeEmptyTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new LocaleDefaultCanNotBeEmpty();
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new LocaleDefaultCanNotBeEmpty();

		$this->assertEquals('The default locale can not be empty.', $exception->getMessage());
	}
}
