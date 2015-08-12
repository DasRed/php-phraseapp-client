<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Synchronize;
use Zend\Log\Logger;
use DasRed\PhraseApp\Request\Locales;
use DasRed\PhraseApp\Request\Keys;
use DasRed\PhraseApp\Request\Translations;
use Zend\Log\Writer\Mock;
use DasRed\PhraseApp\Synchronize\Exception\FailureAddLocale;
use DasRed\PhraseApp\Synchronize\Exception\FailureAddKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureDeleteKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContentByTag;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContent;
use DasRed\PhraseApp\Config;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize
 */
class SynchronizeTest extends \PHPUnit_Framework_TestCase
{
	protected $config;

	protected $logger;

	protected $writer;

	public function setUp()
	{
		parent::setUp();

		$this->config = new Config('pp', 'b', 'de');
		$this->config->setApplicationName('appName')->setBaseUrl('a')->setTagForContentChangeFromLocalToRemote(null);

		$this->logger = new Logger();
		$this->writer = new Mock();
		$this->logger->addWriter($this->writer);
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->config = null;
		$this->logger = null;
		$this->writer = null;
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$sync = new Synchronize($this->logger, $this->config);

		$this->assertSame($this->logger, $sync->getLogger());
		$this->assertSame($this->config, $sync->getConfig());
	}

	/**
	 * @covers ::addTranslation
	 */
	public function testAddTranslation()
	{
		$sync = new Synchronize($this->logger, $this->config);

		$this->assertSame($sync, $sync->addTranslation('de', 'a', 'c'));
		$this->assertEquals(['de' => ['a' => 'c']], $sync->getTranslations());
		$this->assertSame($sync, $sync->addTranslation('de', 'a', 'd'));
		$this->assertEquals(['de' => ['a' => 'd']], $sync->getTranslations());
		$this->assertSame($sync, $sync->addTranslation('de', 'b', 'nuff'));
		$this->assertEquals(['de' => ['a' => 'd', 'b' => 'nuff']], $sync->getTranslations());
		$this->assertSame($sync, $sync->addTranslation('en', 'b', 'nuff'));
		$this->assertEquals(['de' => ['a' => 'd', 'b' => 'nuff'], 'en' => ['b' => 'nuff']], $sync->getTranslations());
	}

	/**
	 * @covers ::addTranslations
	 */
	public function testAddTranslations()
	{
		$sync = new Synchronize($this->logger, $this->config);

		$this->assertSame($sync, $sync->addTranslations('de', ['a' => 'c']));
		$this->assertEquals(['de' => ['a' => 'c']], $sync->getTranslations());

		$this->assertSame($sync, $sync->addTranslations('de', ['a' => 'd', 'b' => 'nuff']));
		$this->assertEquals(['de' => ['a' => 'd', 'b' => 'nuff']], $sync->getTranslations());

		$this->assertSame($sync, $sync->addTranslations('en', ['a' => 'd', 'b' => 'nuff']));
		$this->assertEquals(['de' => ['a' => 'd', 'b' => 'nuff'], 'en' => ['a' => 'd', 'b' => 'nuff']], $sync->getTranslations());
	}

	/**
	 * @covers ::getTranslation
	 */
	public function testGetTranslation()
	{
		$sync = new Synchronize($this->logger, $this->config);

		$this->assertNull($sync->getTranslation('de', 'a'));
		$sync->addTranslation('de', 'a', 'c');
		$this->assertNull($sync->getTranslation('de', 'b'));
		$this->assertSame('c', $sync->getTranslation('de', 'a'));
	}

	/**
	 * @covers ::getTranslationLocales
	 */
	public function testGetTranslationLocales()
	{
		$sync = new Synchronize($this->logger, $this->config);

		$reflectionMethod = new \ReflectionMethod($sync, 'getTranslationLocales');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([], $reflectionMethod->invoke($sync));

		$sync->addTranslations('fr', ['a' => '1', 'b' => '2']);
		$sync->addTranslations('en', ['a' => '1', 'b' => '2']);
		$sync->addTranslations('de', ['a' => '1', 'b' => '2']);
		$sync->addTranslations('us', ['a' => '1', 'b' => '2']);
		$this->assertEquals(['de', 'us', 'en', 'fr'], $reflectionMethod->invoke($sync));
	}

	/**
	 * @covers ::getKeys
	 */
	public function testGetKeys()
	{
		$sync = new Synchronize($this->logger, $this->config);

		$reflectionMethod = new \ReflectionMethod($sync, 'getKeys');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([], $reflectionMethod->invoke($sync));
		$this->assertEquals([], $reflectionMethod->invoke($sync, 'de'));

		$sync->addTranslations('fr', ['a' => '1', 'b' => '2', 'fr' => '2']);
		$sync->addTranslations('en', ['a' => '1', 'b' => '2', 'en' => '3']);
		$sync->addTranslations('de', ['a' => '1', 'b' => '2', 'de' => '4']);

		$this->assertEquals(['a', 'b', 'de', 'en', 'fr'], $reflectionMethod->invoke($sync));
		$this->assertEquals(['a', 'b', 'de'], $reflectionMethod->invoke($sync, 'de'));
		$this->assertEquals([], $reflectionMethod->invoke($sync, 'nu'));
	}

	/**
	 * @covers ::getTranslations
	 */
	public function testGetTranslations()
	{
		$sync = new Synchronize($this->logger, $this->config);

		$this->assertEquals([], $sync->getTranslations());
		$this->assertEquals([], $sync->getTranslations('de'));

		$sync->addTranslations('en', ['en' => '3']);
		$sync->addTranslations('de', ['de' => '4']);

		$this->assertEquals(['en' => ['en' => '3'], 'de' => ['de' => '4']], $sync->getTranslations());
		$this->assertEquals(['de' => '4'], $sync->getTranslations('de'));
		$this->assertEquals([], $sync->getTranslations('nu'));
	}

	/**
	 * @covers ::removeTranslationKeyFromAllLocales
	 */
	public function testRemoveTranslationKeyFromAllLocales()
	{
		$sync = new Synchronize($this->logger, $this->config);

		$reflectionMethod = new \ReflectionMethod($sync, 'removeTranslationKeyFromAllLocales');
		$reflectionMethod->setAccessible(true);

		$sync->addTranslations('fr', ['a' => '1', 'b' => '2', 'fr' => '2']);
		$sync->addTranslations('en', ['a' => '1', 'b' => '2', 'en' => '3']);
		$sync->addTranslations('de', ['a' => '1', 'b' => '2', 'de' => '4']);

		$this->assertSame($sync, $reflectionMethod->invoke($sync, 'nuff'));
		$this->assertEquals([
			'fr' => ['a' => '1', 'b' => '2', 'fr' => '2'],
			'en' => ['a' => '1', 'b' => '2', 'en' => '3'],
			'de' => ['a' => '1', 'b' => '2', 'de' => '4']
		], $sync->getTranslations());

		$this->assertSame($sync, $reflectionMethod->invoke($sync, 'a'));
		$this->assertEquals([
			'fr' => ['b' => '2', 'fr' => '2'],
			'en' => ['b' => '2', 'en' => '3'],
			'de' => ['b' => '2', 'de' => '4']
		], $sync->getTranslations());

		$this->assertSame($sync, $reflectionMethod->invoke($sync, 'de'));
		$this->assertEquals([
			'fr' => ['b' => '2', 'fr' => '2'],
			'en' => ['b' => '2', 'en' => '3'],
			'de' => ['b' => '2']
		], $sync->getTranslations());
	}

	/**
	 * @covers ::synchronize
	 */
	public function testSynchronizeSuccess()
	{
		$builder = $this->getMockBuilder(Synchronize::class)->setMethods(['synchronizeLocales', 'synchronizeKeys', 'synchronizeCleanUpKeys', 'synchronizeContent'])->setConstructorArgs([$this->logger, $this->config]);

		$syncA = $builder->getMock();
		$syncB = $builder->getMock();
		$syncC = $builder->getMock();
		$syncD = $builder->getMock();

		$syncA->expects($this->once())->method('synchronizeLocales')->with()->willReturn($syncB);
		$syncA->expects($this->never())->method('synchronizeKeys');
		$syncA->expects($this->never())->method('synchronizeCleanUpKeys');
		$syncA->expects($this->never())->method('synchronizeContent');

		$syncB->expects($this->never())->method('synchronizeLocales');
		$syncB->expects($this->once())->method('synchronizeKeys')->with()->willReturn($syncC);
		$syncB->expects($this->never())->method('synchronizeCleanUpKeys');
		$syncB->expects($this->never())->method('synchronizeContent');

		$syncC->expects($this->never())->method('synchronizeLocales');
		$syncC->expects($this->never())->method('synchronizeKeys');
		$syncC->expects($this->once())->method('synchronizeCleanUpKeys')->with()->willReturn($syncD);
		$syncC->expects($this->never())->method('synchronizeContent');

		$syncD->expects($this->never())->method('synchronizeLocales');
		$syncD->expects($this->never())->method('synchronizeKeys');
		$syncD->expects($this->never())->method('synchronizeCleanUpKeys');
		$syncD->expects($this->once())->method('synchronizeContent')->with()->willReturn($syncA);

		$this->assertTrue($syncA->synchronize());
	}

	/**
	 * @covers ::synchronize
	 */
	public function testSynchronizeFailed()
	{
		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['synchronizeLocales', 'synchronizeKeys', 'synchronizeContent'])->setConstructorArgs([$this->logger, $this->config])->getMock();

		$sync->expects($this->once())->method('synchronizeLocales')->with()->willThrowException(new \DasRed\PhraseApp\Exception());
		$sync->expects($this->never())->method('synchronizeKeys');
		$sync->expects($this->never())->method('synchronizeContent');

		$this->assertFalse($sync->synchronize());
		$this->assertCount(2, $this->writer->events);
	}

	/**
	 * @covers ::synchronizeCleanUpKeys
	 */
	public function testSynchronizeCleanUpKeys()
	{
		$sync = new Synchronize($this->logger, $this->config);

		$sync->addTranslations('fr', ['a' => '1', 'b' => '2', 'fr' => '2']);
		$sync->addTranslations('en', ['a' => '1', 'b' => '2', 'en' => '3']);
		$sync->addTranslations('de', ['a' => '1', 'b' => '2', 'de' => '4']);

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeCleanUpKeys');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync));
		$this->assertEquals([
			'fr' => ['a' => '1', 'b' => '2'],
			'en' => ['a' => '1', 'b' => '2'],
			'de' => ['a' => '1', 'b' => '2', 'de' => '4'],
		], $sync->getTranslations());
	}

	/**
	 * @covers ::synchronizeContent
	 */
	public function testSynchronizeContentSuccessPrefereRemoteWithTag()
	{
		$remote = [
			'fr' => ['a' => 'fr.remote.1', 'b' => 'fr.remote.2', 'fr' => 'fr.remote.2'],
			'de' => ['a' => 'de.remote.1', 'b' => 'de.remote.2', 'de' => 'de.remote.4']
		];
		$local = [
			'fr' => ['a' => 'fr.locale.1', 'b' => 'fr.locale.2', 'fr' => 'fr.locale.2'],
			'de' => ['a' => 'de.locale.1', 'b' => 'de.locale.2', 'de' => 'de.locale.4', 'nuff' => 'de.locale.nuff']
		];

		$phraseTranslations = $this->getMockBuilder(Translations::class)->setMethods(['fetch', 'store'])->disableOriginalConstructor()->getMock();
		$phraseTranslations->expects($this->once())->method('fetch')->with()->willReturn($remote);
		$phraseTranslations->expects($this->once())->method('store')->with('de', 'nuff', 'de.locale.nuff')->willReturn(true);

		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['addTag'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('addTag')->with('nuff', 'newContent')->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseAppTranslations', 'getPhraseAppKeys'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->exactly(2))->method('getPhraseAppTranslations')->with()->willReturn($phraseTranslations);
		$sync->expects($this->once())->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}

		$this->config->setPreferDirection(Config::PREFER_REMOTE)->setTagForContentChangeFromLocalToRemote('newContent');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeContent');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync));

		$this->assertEquals([
			'fr' => ['a' => 'fr.remote.1', 'b' => 'fr.remote.2', 'fr' => 'fr.remote.2'],
			'de' => ['a' => 'de.remote.1', 'b' => 'de.remote.2', 'de' => 'de.remote.4', 'nuff' => 'de.locale.nuff'],
		], $sync->getTranslations());
	}

	/**
	 * @covers ::synchronizeContent
	 */
	public function testSynchronizeContentSuccessPrefereRemoteWithoutTag()
	{
		$remote = [
			'fr' => ['a' => 'fr.remote.1', 'b' => 'fr.remote.2', 'fr' => 'fr.remote.2'],
			'de' => ['a' => 'de.remote.1', 'b' => 'de.remote.2', 'de' => 'de.remote.4']
		];
		$local = [
			'fr' => ['a' => 'fr.locale.1', 'b' => 'fr.locale.2', 'fr' => 'fr.locale.2'],
			'de' => ['a' => 'de.locale.1', 'b' => 'de.locale.2', 'de' => 'de.locale.4', 'nuff' => 'de.locale.nuff']
		];

		$phraseTranslations = $this->getMockBuilder(Translations::class)->setMethods(['fetch', 'store'])->disableOriginalConstructor()->getMock();
		$phraseTranslations->expects($this->once())->method('fetch')->with()->willReturn($remote);
		$phraseTranslations->expects($this->once())->method('store')->with('de', 'nuff', 'de.locale.nuff')->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseAppTranslations', 'getPhraseAppKeys'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->exactly(2))->method('getPhraseAppTranslations')->with()->willReturn($phraseTranslations);
		$sync->expects($this->never())->method('getPhraseAppKeys');

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}

		$this->config->setPreferDirection(Config::PREFER_REMOTE);

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeContent');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync));

		$this->assertEquals([
			'fr' => ['a' => 'fr.remote.1', 'b' => 'fr.remote.2', 'fr' => 'fr.remote.2'],
			'de' => ['a' => 'de.remote.1', 'b' => 'de.remote.2', 'de' => 'de.remote.4', 'nuff' => 'de.locale.nuff'],
		], $sync->getTranslations());
	}

	/**
	 * @covers ::synchronizeContent
	 */
	public function testSynchronizeContentSuccessPrefereLocal()
	{
		$remote = [
			'fr' => ['a' => 'fr.remote.1', 'b' => 'fr.remote.2', 'fr' => 'fr.remote.2'],
			'de' => ['a' => 'de.remote.1', 'b' => 'de.remote.2', 'de' => 'de.remote.4']
		];
		$local = [
			'fr' => ['a' => 'fr.locale.1', 'b' => 'fr.locale.2', 'fr' => 'fr.locale.2'],
			'de' => ['a' => 'de.locale.1', 'b' => 'de.locale.2', 'de' => 'de.locale.4', 'nuff' => 'de.locale.nuff']
		];

		$phraseTranslations = $this->getMockBuilder(Translations::class)->setMethods(['fetch', 'store'])->disableOriginalConstructor()->getMock();
		$phraseTranslations->expects($this->once())->method('fetch')->with()->willReturn($remote);
		$phraseTranslations->expects($this->exactly(7))->method('store')->withConsecutive(
			['de', 'a', 'de.locale.1'],
			['de', 'b', 'de.locale.2'],
			['de', 'de', 'de.locale.4'],
			['de', 'nuff', 'de.locale.nuff'],

			['fr', 'a', 'fr.locale.1'],
			['fr', 'b', 'fr.locale.2'],
			['fr', 'fr', 'fr.locale.2']
		)->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseAppTranslations'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->exactly(8))->method('getPhraseAppTranslations')->with()->willReturn($phraseTranslations);

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}

		$this->config->setPreferDirection(Config::PREFER_LOCAL);

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeContent');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync));

		$this->assertEquals([
			'fr' => ['a' => 'fr.locale.1', 'b' => 'fr.locale.2', 'fr' => 'fr.locale.2'],
			'de' => ['a' => 'de.locale.1', 'b' => 'de.locale.2', 'de' => 'de.locale.4', 'nuff' => 'de.locale.nuff'],
		], $sync->getTranslations());
	}

	/**
	 * @covers ::synchronizeContent
	 */
	public function testSynchronizeContentFailedByTag()
	{
		$remote = [
			'fr' => ['a' => 'fr.remote.1', 'b' => 'fr.remote.2', 'fr' => 'fr.remote.2'],
			'de' => ['a' => 'de.remote.1', 'b' => 'de.remote.2', 'de' => 'de.remote.4']
		];
		$local = [
			'fr' => ['a' => 'fr.locale.1', 'b' => 'fr.locale.2', 'fr' => 'fr.locale.2'],
			'de' => ['a' => 'de.locale.1', 'b' => 'de.locale.2', 'de' => 'de.locale.4', 'nuff' => 'de.locale.nuff']
		];

		$phraseTranslations = $this->getMockBuilder(Translations::class)->setMethods(['fetch', 'store'])->disableOriginalConstructor()->getMock();
		$phraseTranslations->expects($this->once())->method('fetch')->with()->willReturn($remote);
		$phraseTranslations->expects($this->never())->method('store');

		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['addTag'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('addTag')->with('nuff', 'newContent')->willReturn(false);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseAppTranslations', 'getPhraseAppKeys'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getPhraseAppTranslations')->with()->willReturn($phraseTranslations);
		$sync->expects($this->once())->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}

		$this->config->setPreferDirection(Config::PREFER_REMOTE)->setTagForContentChangeFromLocalToRemote('newContent');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeContent');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(FailureStoreContentByTag::class);
		$reflectionMethod->invoke($sync);
	}


	/**
	 * @covers ::synchronizeContent
	 */
	public function testSynchronizeContentFailedByStore()
	{
		$remote = [
			'fr' => ['a' => 'fr.remote.1', 'b' => 'fr.remote.2', 'fr' => 'fr.remote.2'],
			'de' => ['a' => 'de.remote.1', 'b' => 'de.remote.2', 'de' => 'de.remote.4']
		];
		$local = [
			'fr' => ['a' => 'fr.locale.1', 'b' => 'fr.locale.2', 'fr' => 'fr.locale.2'],
			'de' => ['a' => 'de.locale.1', 'b' => 'de.locale.2', 'de' => 'de.locale.4', 'nuff' => 'de.locale.nuff']
		];

		$phraseTranslations = $this->getMockBuilder(Translations::class)->setMethods(['fetch', 'store'])->disableOriginalConstructor()->getMock();
		$phraseTranslations->expects($this->once())->method('fetch')->with()->willReturn($remote);
		$phraseTranslations->expects($this->once())->method('store')->with('de', 'nuff', 'de.locale.nuff')->willReturn(false);

		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['addTag'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('addTag')->with('nuff', 'newContent')->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseAppTranslations', 'getPhraseAppKeys'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->exactly(2))->method('getPhraseAppTranslations')->with()->willReturn($phraseTranslations);
		$sync->expects($this->once())->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}

		$this->config->setPreferDirection(Config::PREFER_REMOTE)->setTagForContentChangeFromLocalToRemote('newContent');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeContent');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(FailureStoreContent::class);
		$reflectionMethod->invoke($sync);
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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseAppKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(4))->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);
		$sync->expects($this->never())->method('removeTranslationKeyFromAllLocales');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeKeys');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(FailureDeleteKey::class);
		$reflectionMethod->invoke($sync);
	}

	/**
	 * @covers ::synchronizeLocales
	 */
	public function testSynchronizeLocalesSuccess()
	{
		$localesLocale = ['de', 'en', 'ru'];
		$localesRemote = ['de', 'fr', 'us'];

		$translations = ['a' => '1', 'b' => '2', 'de' => '4'];

		$phraseLocales = $this->getMockBuilder(Locales::class)->setMethods(['fetch', 'create'])->disableOriginalConstructor()->getMock();
		$phraseLocales->expects($this->once())->method('fetch')->with()->willReturn($localesRemote);
		$phraseLocales->expects($this->exactly(2))->method('create')->withConsecutive(['en'], ['ru'])->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslationLocales', 'getPhraseAppLocales', 'getTranslations'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getTranslationLocales')->with()->willReturn($localesLocale);
		$sync->expects($this->exactly(3))->method('getPhraseAppLocales')->with()->willReturn($phraseLocales);
		$sync->expects($this->once())->method('getTranslations')->with('de')->willReturn($translations);

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeLocales');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($sync, 'translations');
		$reflectionProperty->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync));
		$this->assertEquals([
			'fr' => ['a' => '', 'b' => '', 'de' => ''],
			'us' => ['a' => '', 'b' => '', 'de' => ''],
		], $reflectionProperty->getValue($sync));
	}

	/**
	 * @covers ::synchronizeLocales
	 */
	public function testSynchronizeLocalesFailed()
	{
		$localesLocale = ['de', 'en', 'ru'];
		$localesRemote = ['de', 'fr', 'us'];

		$translations = ['a' => '1', 'b' => '2', 'de' => '4'];

		$phraseLocales = $this->getMockBuilder(Locales::class)->setMethods(['fetch', 'create'])->disableOriginalConstructor()->getMock();
		$phraseLocales->expects($this->once())->method('fetch')->with()->willReturn($localesRemote);
		$phraseLocales->expects($this->once())->method('create')->withConsecutive(['en'], ['ru'])->willReturn(false);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslationLocales', 'getPhraseAppLocales', 'getTranslations'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getTranslationLocales')->with()->willReturn($localesLocale);
		$sync->expects($this->exactly(2))->method('getPhraseAppLocales')->with()->willReturn($phraseLocales);
		$sync->expects($this->never())->method('getTranslations');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeLocales');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($sync, 'translations');
		$reflectionProperty->setAccessible(true);

		$this->setExpectedException(FailureAddLocale::class);
		$reflectionMethod->invoke($sync);
		$this->assertEquals([], $reflectionProperty->getValue($sync));
	}

	/**
	 * @covers ::synchronizeKeysCreateKey
	 */
	public function testSynchronizeKeysCreateKeySuccess()
	{
		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('create')->with('nuff')->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseAppKeys'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeKeysCreateKey');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($sync, $reflectionMethod->invoke($sync, 'nuff'));
	}

	/**
	 * @covers ::synchronizeKeysCreateKey
	 */
	public function testSynchronizeKeysCreateKeyFailed()
	{
		$phraseKeys = $this->getMockBuilder(Keys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseKeys->expects($this->once())->method('create')->with('nuff')->willReturn(false);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseAppKeys'])->setConstructorArgs([$this->logger, $this->config])->getMock();
		$sync->expects($this->once())->method('getPhraseAppKeys')->with()->willReturn($phraseKeys);

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeKeysCreateKey');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(FailureAddKey::class);
		$reflectionMethod->invoke($sync, 'nuff');
	}
}
