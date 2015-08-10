<?php
namespace DasRedTest\PhraseApp\Synchronize\Exception;

use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContentByTag;
use DasRed\PhraseApp\Synchronize\Exception;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Exception\FailureStoreContentByTag
 */
class FailureStoreContentByTagTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new FailureStoreContentByTag('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new FailureStoreContentByTag('nuff');

		$this->assertEquals('Can not store translation content for translation key "nuff" because the tag for content change from local to remote can not be setted.', $exception->getMessage());
	}
}
