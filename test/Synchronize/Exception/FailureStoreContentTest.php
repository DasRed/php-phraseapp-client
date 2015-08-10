<?php
namespace DasRedTest\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContent;
use DasRed\PhraseApp\Synchronize\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Exception\FailureStoreContent
 */
class FailureStoreContentTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new FailureStoreContent('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new FailureStoreContent('nuff');

		$this->assertEquals('Can not store translation content for translation key "nuff".', $exception->getMessage());
	}
}
