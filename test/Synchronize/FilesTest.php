<?php
namespace DasRedTest\PhraseApp\Synchronize;

use DasRed\PhraseApp\Synchronize\Files;
use DasRed\PhraseApp\Synchronize\Files\HandlerInterface;
use Zend\Log\Logger;
use DasRed\PhraseApp\Request\Keys;
use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\Synchronize\Exception\FailureAddKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureDeleteKey;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize\Files
 */
class FilesTest extends \PHPUnit_Framework_TestCase
{
	protected $config;

	protected $logger;

	public function setUp()
	{
		parent::setUp();

		$this->config = new Config('pp', 'b', 'de');
		$this->config->setApplicationName('appName')->setBaseUrl('a');

		$this->logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->config = null;
		$this->logger = null;
	}

	/**
	 * @covers ::appendHandler
	 */
	public function testAppendHandler()
	{
		$handlerA = $this->getMockBuilder(HandlerInterface::class)->getMockForAbstractClass();
		$handlerB = $this->getMockBuilder(HandlerInterface::class)->getMockForAbstractClass();

		$files = new Files($this->logger, $this->config);

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

		$files = new Files($this->logger, $this->config);

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
		$files = $this->getMockBuilder(Files::class)->setMethods(['read', 'write', 'synchronizeLocales', 'synchronizeKeys', 'synchronizeContent'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$files->expects($this->once())->method('read')->with()->willReturnSelf();
		$files->expects($this->once())->method('write')->with()->willReturnSelf();
		$files->expects($this->any())->method('synchronizeLocales')->willReturnSelf();
		$files->expects($this->any())->method('synchronizeKeys')->willReturnSelf();
		$files->expects($this->any())->method('synchronizeContent')->willReturnSelf();

		$this->assertSame($files, $files->synchronize());
	}

	/**
	 * @covers ::synchronizeKeysCreateKey
	 */
	public function testSynchronizeKeysCreateKeySuccess()
	{
		$key = 'abc.def';
		$callOrder = [];

		$translationKeys = $this->getMockBuilder(Keys::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
		$translationKeys->expects($this->once())->method('create')->with(
			$this->identicalTo($key),
			$this->identicalTo('ABb')
		)->willReturn(true);

		$files = $this->getMockBuilder(Files::class)->setMethods(['getPhraseAppKeys'])->disableOriginalConstructor()->getMock();
		$files->expects($this->once())->method('getPhraseAppKeys')->with()->willReturn($translationKeys);

		$builder = $this->getMockBuilder(HandlerInterface::class)->setMethods(['getDescriptionForKey']);

		$handlerA = $builder->getMockForAbstractClass();
		$handlerA->expects($this->exactly(2))->method('getDescriptionForKey')->with($this->callback(function($keyArg) use (&$callOrder, $key)
		{
			$this->assertSame($key, $keyArg);
			$callOrder[] = 'A';

			return true;
		}))->willReturnOnConsecutiveCalls('A', '');

		$handlerB = $builder->getMockForAbstractClass();
		$handlerB->expects($this->exactly(2))->method('getDescriptionForKey')->with($this->callback(function($keyArg) use (&$callOrder, $key)
		{
			$this->assertSame($key, $keyArg);
			$callOrder[] = 'B';

			return true;
		}))->willReturnOnConsecutiveCalls('B', 'b');

		$reflectionMethod = new \ReflectionMethod($files, 'synchronizeKeysCreateKey');
		$reflectionMethod->setAccessible(true);

		$files->appendHandler($handlerA);
		$files->appendHandler($handlerB);
		$files->appendHandler($handlerA);
		$files->appendHandler($handlerB);

		$this->assertSame($files, $reflectionMethod->invoke($files, $key));
		$this->assertEquals(['A', 'B', 'A', 'B'], $callOrder);
	}

	/**
	 * @covers ::synchronizeKeysCreateKey
	 */
	public function testSynchronizeKeysCreateKeyFailed()
	{
		$key = 'abc.def';
		$callOrder = [];

		$translationKeys = $this->getMockBuilder(Keys::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
		$translationKeys->expects($this->once())->method('create')->with(
			$this->identicalTo($key),
			$this->identicalTo('ABb')
		)->willReturn(false);

		$files = $this->getMockBuilder(Files::class)->setMethods(['getPhraseAppKeys'])->disableOriginalConstructor()->getMock();
		$files->expects($this->once())->method('getPhraseAppKeys')->with()->willReturn($translationKeys);

		$builder = $this->getMockBuilder(HandlerInterface::class)->setMethods(['getDescriptionForKey']);

		$handlerA = $builder->getMockForAbstractClass();
		$handlerA->expects($this->exactly(2))->method('getDescriptionForKey')->with($this->callback(function($keyArg) use (&$callOrder, $key)
		{
			$this->assertSame($key, $keyArg);
			$callOrder[] = 'A';

			return true;
		}))->willReturnOnConsecutiveCalls('A', '');

		$handlerB = $builder->getMockForAbstractClass();
		$handlerB->expects($this->exactly(2))->method('getDescriptionForKey')->with($this->callback(function($keyArg) use (&$callOrder, $key)
		{
			$this->assertSame($key, $keyArg);
			$callOrder[] = 'B';

			return true;
		}))->willReturnOnConsecutiveCalls('B', 'b');

		$reflectionMethod = new \ReflectionMethod($files, 'synchronizeKeysCreateKey');
		$reflectionMethod->setAccessible(true);

		$files->appendHandler($handlerA);
		$files->appendHandler($handlerB);
		$files->appendHandler($handlerA);
		$files->appendHandler($handlerB);

		$this->setExpectedException(FailureAddKey::class);
		$reflectionMethod->invoke($files, $key);
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		$callOrder = [];

		$files = new Files($this->logger, $this->config);

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

	/**
	 * @covers ::synchronizeKeys
	 */
	public function testSynchronizeKeysSuccess()
	{
		$keysLocal = ['b' => '2', 'de' => '4', 'en' => '2', 'fr' => 'narf'];
		$keysRemote = ['b', 'de', 'nuff', 'narf'];

		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseKeys->expects($this->exactly(2))->method('create')->withConsecutive(['en'], ['fr'])->willReturn(true);
		$phraseKeys->expects($this->exactly(2))->method('delete')->withConsecutive(['nuff'], ['narf'])->willReturn(true);

		$sync = $this->getMockBuilder(Files::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(5))->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);
		$sync->expects($this->exactly(2))->method('removeTranslationKeyFromAllLocales')->withConsecutive(['nuff'], ['narf'])->willReturnSelf();

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeKeys');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync));
	}

	/**
	 * @covers ::synchronizeKeys
	 */
	public function testSynchronizeKeysSuccessWithoutCreate()
	{
		$keysLocal = ['b' => '2', 'de' => '4'];
		$keysRemote = ['b', 'de', 'nuff', 'narf'];

		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseKeys->expects($this->never())->method('create');
		$phraseKeys->expects($this->exactly(2))->method('delete')->withConsecutive(['nuff'], ['narf'])->willReturn(true);

		$sync = $this->getMockBuilder(Files::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(3))->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);
		$sync->expects($this->exactly(2))->method('removeTranslationKeyFromAllLocales')->withConsecutive(['nuff'], ['narf'])->willReturnSelf();

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeKeys');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync));
	}

	/**
	 * @covers ::synchronizeKeys
	 */
	public function testSynchronizeKeysSuccessWithoutDelete()
	{
		$keysLocal = ['b' => '2', 'de' => '4', 'en' => '2', 'fr' => 'narf'];
		$keysRemote = ['b', 'de'];

		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseKeys->expects($this->exactly(2))->method('create')->withConsecutive(['en'], ['fr'])->willReturn(true);
		$phraseKeys->expects($this->never())->method('delete');

		$sync = $this->getMockBuilder(Files::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(3))->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);
		$sync->expects($this->never())->method('removeTranslationKeyFromAllLocales');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeKeys');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync));
	}

	/**
	 * @covers ::synchronizeKeys
	 */
	public function testSynchronizeKeysSuccessWithoutCreateAndDelete()
	{
		$keysLocal = ['b' => '2', 'de' => '4'];
		$keysRemote = ['b', 'de'];

		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseKeys->expects($this->never())->method('create');
		$phraseKeys->expects($this->never())->method('delete');

		$sync = $this->getMockBuilder(Files::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->once())->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);
		$sync->expects($this->never())->method('removeTranslationKeyFromAllLocales');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeKeys');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync));
	}

	/**
	 * @covers ::synchronizeKeys
	 */
	public function testSynchronizeKeysFailedByCreate()
	{
		$keysLocal = ['b' => '2', 'de' => '4', 'en' => '2', 'fr' => 'narf'];
		$keysRemote = ['b', 'de', 'nuff', 'narf'];

		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseKeys->expects($this->once())->method('create')->with('en')->willReturn(false);
		$phraseKeys->expects($this->never())->method('delete');

		$sync = $this->getMockBuilder(Files::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(2))->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);
		$sync->expects($this->never())->method('removeTranslationKeyFromAllLocales');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeKeys');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(FailureAddKey::class);
		$reflectionMethod->invoke($sync);
	}

	/**
	 * @covers ::synchronizeKeys
	 */
	public function testSynchronizeKeysFailedByDelete()
	{
		$keysLocal = ['b' => '2', 'de' => '4', 'en' => '2', 'fr' => 'narf'];
		$keysRemote = ['b', 'de', 'nuff', 'narf'];

		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseKeys->expects($this->exactly(2))->method('create')->withConsecutive(['en'], ['fr'])->willReturn(true);
		$phraseKeys->expects($this->once())->method('delete')->with('nuff')->willReturn(false);

		$sync = $this->getMockBuilder(Files::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(4))->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);
		$sync->expects($this->never())->method('removeTranslationKeyFromAllLocales');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeKeys');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(FailureDeleteKey::class);
		$reflectionMethod->invoke($sync);
	}
}
