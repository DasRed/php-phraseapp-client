<?php
namespace DasRedTest\PhraseApp\Synchronize;

use DasRed\PhraseApp\Synchronize\Files;
use DasRed\PhraseApp\Synchronize\Files\HandlerInterface;
use Zend\Log\Logger;
use DasRed\PhraseApp\TranslationKeys;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Files
 */
class FilesTest extends \PHPUnit_Framework_TestCase
{
	protected $logger;

	public function setUp()
	{
		parent::setUp();

		$this->logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->logger = null;
	}

	/**
	 * @covers ::appendHandler
	 */
	public function testAppendHandler()
	{
		$handlerA = $this->getMockBuilder(HandlerInterface::class)->getMockForAbstractClass();
		$handlerB = $this->getMockBuilder(HandlerInterface::class)->getMockForAbstractClass();

		$files = new Files($this->logger, '', '', '', '', '');

		$reflectionProperty = new \ReflectionProperty($files, 'handlers');
		$reflectionProperty->setAccessible(true);

		$this->assertEquals([], $reflectionProperty->getValue($files));

		$this->assertSame($files, $files->appendHandler($handlerA));
		$this->assertCount(1, $reflectionProperty->getValue($files));
		$this->assertSame($handlerA, $reflectionProperty->getValue($files)[0]);

		$this->assertSame($files, $files->appendHandler($handlerB));
		$this->assertCount(2, $reflectionProperty->getValue($files));
		$this->assertSame($handlerA, $reflectionProperty->getValue($files)[0]);
		$this->assertSame($handlerB, $reflectionProperty->getValue($files)[1]);

		$this->assertSame($files, $files->appendHandler($handlerA));
		$this->assertCount(3, $reflectionProperty->getValue($files));
		$this->assertSame($handlerA, $reflectionProperty->getValue($files)[0]);
		$this->assertSame($handlerB, $reflectionProperty->getValue($files)[1]);
		$this->assertSame($handlerA, $reflectionProperty->getValue($files)[2]);
	}

	/**
	 * @covers ::read
	 */
	public function testRead()
	{
		$callOrder = [];

		$files = new Files($this->logger, '', '', '', '', '');

		$builder = $this->getMockBuilder(HandlerInterface::class)->setMethods(['read']);
		$handlerA = $builder->getMockForAbstractClass();
		$handlerA->expects($this->exactly(2))->method('read')->with($this->callback(function($filesArg) use (&$callOrder, $files)
		{
			$this->assertSame($files, $filesArg);
			$callOrder[] = 'A';

			return true;
		}))->willReturn(true);

		$handlerB = $builder->getMockForAbstractClass();
		$handlerB->expects($this->once())->method('read')->with($this->callback(function($filesArg) use (&$callOrder, $files)
		{
			$this->assertSame($files, $filesArg);
			$callOrder[] = 'B';

			return true;
		}))->willReturn(true);

		$reflectionMethod = new \ReflectionMethod($files, 'read');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($files, $reflectionMethod->invoke($files));

		$files->appendHandler($handlerA);
		$files->appendHandler($handlerB);
		$files->appendHandler($handlerA);

		$this->assertSame($files, $reflectionMethod->invoke($files));
		$this->assertEquals(['A', 'B', 'A'], $callOrder);
	}

	/**
	 * @covers ::synchronize
	 */
	public function testSynchronizeSuccess()
	{
		$files = $this->getMockBuilder(Files::class)->setMethods(['read', 'write', 'synchronizeLocales', 'synchronizeKeys', 'synchronizeContent'])->disableOriginalConstructor()->getMock();
		$files->expects($this->once())->method('read')->with()->willReturnSelf();
		$files->expects($this->once())->method('write')->with()->willReturnSelf();
		$files->expects($this->any())->method('synchronizeLocales')->willReturnSelf();
		$files->expects($this->any())->method('synchronizeKeys')->willReturnSelf();
		$files->expects($this->any())->method('synchronizeContent')->willReturnSelf();

		$this->assertTrue($files->synchronize());
	}

	/**
	 * @covers ::synchronize
	 */
	public function testSynchronizeFailed()
	{
		$files = $this->getMockBuilder(Files::class)->setMethods(['read', 'write', 'synchronizeLocales', 'synchronizeKeys', 'synchronizeContent'])->disableOriginalConstructor()->getMock();
		$files->expects($this->once())->method('read')->with()->willReturnSelf();
		$files->expects($this->never())->method('write')->with()->willReturnSelf();
		$files->expects($this->any())->method('synchronizeLocales')->willThrowException(new \DasRed\PhraseApp\Exception());
		$files->expects($this->any())->method('synchronizeKeys')->willReturnSelf();
		$files->expects($this->any())->method('synchronizeContent')->willReturnSelf();

		$this->assertFalse($files->synchronize());
	}

	/**
	 * @covers ::synchronizeKeysCreateKey
	 */
	public function testSynchronizeKeysCreateKey()
	{
		$key = 'abc.def';
		$callOrder = [];

		$translationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
		$translationKeys->expects($this->once())->method('create')->with(
			$this->identicalTo($key),
			$this->identicalTo('ABb'),
			['A', 'Z', 'Z1', 'Z2', 'Z10', 'Z12', 'z']
		)->willReturn(true);

		$files = $this->getMockBuilder(Files::class)->setMethods(['getPhraseTranslationKeys'])->disableOriginalConstructor()->getMock();
		$files->expects($this->once())->method('getPhraseTranslationKeys')->with()->willReturn($translationKeys);

		$builder = $this->getMockBuilder(HandlerInterface::class)->setMethods(['getDescriptionForKey', 'getTagsForKey']);

		$handlerA = $builder->getMockForAbstractClass();
		$handlerA->expects($this->exactly(2))->method('getDescriptionForKey')->with($this->callback(function($keyArg) use (&$callOrder, $key)
		{
			$this->assertSame($key, $keyArg);
			$callOrder[] = 'A';

			return true;
		}))->willReturnOnConsecutiveCalls('A', '');

		$handlerA->expects($this->exactly(2))->method('getTagsForKey')->with($this->callback(function($keyArg) use (&$callOrder, $key)
		{
			$this->assertSame($key, $keyArg);
			$callOrder[] = 'A';

			return true;
		}))->willReturnOnConsecutiveCalls(['Z', 'z'], ['Z2', 'Z12', 'Z1', 'Z10']);

		$handlerB = $builder->getMockForAbstractClass();
		$handlerB->expects($this->exactly(2))->method('getDescriptionForKey')->with($this->callback(function($keyArg) use (&$callOrder, $key)
		{
			$this->assertSame($key, $keyArg);
			$callOrder[] = 'B';

			return true;
		}))->willReturnOnConsecutiveCalls('B', 'b');
		$handlerB->expects($this->exactly(2))->method('getTagsForKey')->with($this->callback(function($keyArg) use (&$callOrder, $key)
		{
			$this->assertSame($key, $keyArg);
			$callOrder[] = 'B';

			return true;
		}))->willReturnOnConsecutiveCalls(['A', 'z'], []);

		$reflectionMethod = new \ReflectionMethod($files, 'synchronizeKeysCreateKey');
		$reflectionMethod->setAccessible(true);

		$files->appendHandler($handlerA);
		$files->appendHandler($handlerB);
		$files->appendHandler($handlerA);
		$files->appendHandler($handlerB);

		$this->assertTrue($reflectionMethod->invoke($files, $key));
		$this->assertEquals(['A', 'A', 'B', 'B', 'A', 'A', 'B', 'B'], $callOrder);
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		$callOrder = [];

		$files = new Files($this->logger, '', '', '', '', '');

		$builder = $this->getMockBuilder(HandlerInterface::class)->setMethods(['write']);
		$handlerA = $builder->getMockForAbstractClass();
		$handlerA->expects($this->exactly(2))->method('write')->with($this->callback(function($filesArg) use (&$callOrder, $files)
		{
			$this->assertSame($files, $filesArg);
			$callOrder[] = 'A';

			return true;
		}))->willReturn(true);

		$handlerB = $builder->getMockForAbstractClass();
		$handlerB->expects($this->once())->method('write')->with($this->callback(function($filesArg) use (&$callOrder, $files)
		{
			$this->assertSame($files, $filesArg);
			$callOrder[] = 'B';

			return true;
		}))->willReturn(true);

		$reflectionMethod = new \ReflectionMethod($files, 'write');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($files, $reflectionMethod->invoke($files));

		$files->appendHandler($handlerA);
		$files->appendHandler($handlerB);
		$files->appendHandler($handlerA);

		$this->assertSame($files, $reflectionMethod->invoke($files));
		$this->assertEquals(['A', 'B', 'A'], $callOrder);
	}
}
