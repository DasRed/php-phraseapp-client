<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Synchronize;
use Zend\Log\Logger;
use DasRed\PhraseApp\Synchronize\Exception\InvalidPreferDirection;
use DasRed\PhraseApp\Locales;
use DasRed\PhraseApp\TranslationKeys;
use DasRed\PhraseApp\Translations;
use Zend\Log\Writer\Mock;
use DasRed\PhraseApp\Synchronize\Exception\FailureAddLocale;
use DasRed\PhraseApp\Synchronize\Exception\FailureAddKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureDeleteKey;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContentByTag;
use DasRed\PhraseApp\Synchronize\Exception\FailureStoreContent;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Synchronize
 */
class SynchronizeTest extends \PHPUnit_Framework_TestCase
{
	protected $logger;

	protected $writer;

	public function setUp()
	{
		parent::setUp();

		$this->logger = new Logger();
		$this->writer = new Mock();
		$this->logger->addWriter($this->writer);
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->logger = null;
		$this->writer = null;
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$this->assertSame($this->logger, $sync->getLogger());
		$this->assertSame('a', $sync->getBaseUrl());
		$this->assertSame('b', $sync->getAuthToken());
		$this->assertSame('c', $sync->getUserEmail());
		$this->assertSame('d', $sync->getUserPassword());
		$this->assertSame('e', $sync->getLocaleDefault());
	}

	/**
	 * @covers ::addTranslation
	 */
	public function testAddTranslation()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

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
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$this->assertSame($sync, $sync->addTranslations('de', ['a' => 'c']));
		$this->assertEquals(['de' => ['a' => 'c']], $sync->getTranslations());

		$this->assertSame($sync, $sync->addTranslations('de', ['a' => 'd', 'b' => 'nuff']));
		$this->assertEquals(['de' => ['a' => 'd', 'b' => 'nuff']], $sync->getTranslations());

		$this->assertSame($sync, $sync->addTranslations('en', ['a' => 'd', 'b' => 'nuff']));
		$this->assertEquals(['de' => ['a' => 'd', 'b' => 'nuff'], 'en' => ['a' => 'd', 'b' => 'nuff']], $sync->getTranslations());
	}

	/**
	 * @covers ::getAuthToken
	 * @covers ::setAuthToken
	 */
	public function testGetSetAuthToken()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$reflectionMethod = new \ReflectionMethod($sync, 'setAuthToken');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('b', $sync->getAuthToken());
		$this->assertSame($sync, $reflectionMethod->invoke($sync, 'nuff'));
		$this->assertSame('nuff', $sync->getAuthToken());
	}

	/**
	 * @covers ::getBaseUrl
	 * @covers ::setBaseUrl
	 */
	public function testGetSetBaseUrl()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$reflectionMethod = new \ReflectionMethod($sync, 'setBaseUrl');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('a', $sync->getBaseUrl());
		$this->assertSame($sync, $reflectionMethod->invoke($sync, 'nuff'));
		$this->assertSame('nuff', $sync->getBaseUrl());
	}

	/**
	 * @covers ::getLogger
	 * @covers ::setLogger
	 */
	public function testGetSetLogger()
	{
		$logger = new Logger();

		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$reflectionMethod = new \ReflectionMethod($sync, 'setLogger');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($this->logger, $sync->getLogger());
		$this->assertSame($sync, $reflectionMethod->invoke($sync, $logger));
		$this->assertSame($logger, $sync->getLogger());
	}

	/**
	 * @covers ::getLocaleDefault
	 * @covers ::setLocaleDefault
	 */
	public function testGetSetLocaleDefault()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$reflectionMethod = new \ReflectionMethod($sync, 'setLocaleDefault');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('e', $sync->getLocaleDefault());
		$this->assertSame($sync, $reflectionMethod->invoke($sync, 'nuff'));
		$this->assertSame('nuff', $sync->getLocaleDefault());
	}

	/**
	 * @covers ::getPhraseLocales
	 */
	public function testGetPhraseLocales()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$reflectionMethod = new \ReflectionMethod($sync, 'getPhraseLocales');
		$reflectionMethod->setAccessible(true);

		$client = $reflectionMethod->invoke($sync);

		$this->assertInstanceOf(Locales::class, $client);
		$this->assertSame($client, $reflectionMethod->invoke($sync));
		$this->assertSame('a/', $client->getBaseUrl());
		$this->assertSame('b', $client->getAuthToken());
	}

	/**
	 * @covers ::getPhraseTranslations
	 */
	public function testGetPhraseTranslations()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$reflectionMethod = new \ReflectionMethod($sync, 'getPhraseTranslations');
		$reflectionMethod->setAccessible(true);

		$client = $reflectionMethod->invoke($sync);

		$this->assertInstanceOf(Translations::class, $client);
		$this->assertSame($client, $reflectionMethod->invoke($sync));
		$this->assertSame('a/', $client->getBaseUrl());
		$this->assertSame('b', $client->getAuthToken());
	}

	/**
	 * @covers ::getPhraseTranslationKeys
	 */
	public function testGetPhraseTranslationKeys()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$reflectionMethod = new \ReflectionMethod($sync, 'getPhraseTranslationKeys');
		$reflectionMethod->setAccessible(true);

		$client = $reflectionMethod->invoke($sync);

		$this->assertInstanceOf(TranslationKeys::class, $client);
		$this->assertSame($client, $reflectionMethod->invoke($sync));
		$this->assertSame('a/', $client->getBaseUrl());
		$this->assertSame('b', $client->getAuthToken());
		$this->assertSame('c', $client->getUserEmail());
		$this->assertSame('d', $client->getUserPassword());
	}

	/**
	 * @covers ::getPreferDirection
	 * @covers ::setPreferDirection
	 */
	public function testGetSetPreferDirectionSuccess()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$this->assertSame(Synchronize::PREFER_REMOTE, $sync->getPreferDirection());
		$this->assertSame($sync, $sync->setPreferDirection(Synchronize::PREFER_LOCAL));
		$this->assertSame(Synchronize::PREFER_LOCAL, $sync->getPreferDirection());
		$this->assertSame($sync, $sync->setPreferDirection(Synchronize::PREFER_REMOTE));
		$this->assertSame(Synchronize::PREFER_REMOTE, $sync->getPreferDirection());
	}

	/**
	 * @covers ::getPreferDirection
	 * @covers ::setPreferDirection
	 */
	public function testGetSetPreferDirectionFailed()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$this->setExpectedException(InvalidPreferDirection::class);
		$sync->setPreferDirection('nuff');
	}

	/**
	 * @covers ::getTagForContentChangeFromLocalToRemote
	 * @covers ::setTagForContentChangeFromLocalToRemote
	 */
	public function testGetSetTagForContentChangeFromLocalToRemote()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$this->assertNull($sync->getTagForContentChangeFromLocalToRemote());
		$this->assertSame($sync, $sync->setTagForContentChangeFromLocalToRemote('nuff'));
		$this->assertSame('nuff', $sync->getTagForContentChangeFromLocalToRemote());
		$this->assertSame($sync, $sync->setTagForContentChangeFromLocalToRemote('narf'));
		$this->assertSame('narf', $sync->getTagForContentChangeFromLocalToRemote());
	}

	/**
	 * @covers ::getTranslation
	 */
	public function testGetTranslation()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

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
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'de');

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
	 * @covers ::getTranslationKeys
	 */
	public function testGetTranslationKeys()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'de');

		$reflectionMethod = new \ReflectionMethod($sync, 'getTranslationKeys');
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
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'de');

		$this->assertEquals([], $sync->getTranslations());
		$this->assertEquals([], $sync->getTranslations('de'));

		$sync->addTranslations('en', ['en' => '3']);
		$sync->addTranslations('de', ['de' => '4']);

		$this->assertEquals(['en' => ['en' => '3'], 'de' => ['de' => '4']], $sync->getTranslations());
		$this->assertEquals(['de' => '4'], $sync->getTranslations('de'));
		$this->assertEquals([], $sync->getTranslations('nu'));
	}

	/**
	 * @covers ::getUserEmail
	 * @covers ::setUserEmail
	 */
	public function testGetSetUserEmail()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$reflectionMethod = new \ReflectionMethod($sync, 'setUserEmail');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('c', $sync->getUserEmail());
		$this->assertSame($sync, $reflectionMethod->invoke($sync, 'nuff'));
		$this->assertSame('nuff', $sync->getUserEmail());
	}

	/**
	 * @covers ::getUserPassword
	 * @covers ::setUserPassword
	 */
	public function testGetSetUserPassword()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'e');

		$reflectionMethod = new \ReflectionMethod($sync, 'setUserPassword');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('d', $sync->getUserPassword());
		$this->assertSame($sync, $reflectionMethod->invoke($sync, 'nuff'));
		$this->assertSame('nuff', $sync->getUserPassword());
	}

	/**
	 * @covers ::removeTranslationKeyFromAllLocales
	 */
	public function testRemoveTranslationKeyFromAllLocales()
	{
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'de');

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
		$builder = $this->getMockBuilder(Synchronize::class)->setMethods(['synchronizeLocales', 'synchronizeKeys', 'synchronizeCleanUpKeys', 'synchronizeContent'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de']);

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
		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['synchronizeLocales', 'synchronizeKeys', 'synchronizeContent'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();

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
		$sync = new Synchronize($this->logger, 'a', 'b', 'c', 'd', 'de');

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

		$phraseTranslationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['addTag'])->disableOriginalConstructor()->getMock();
		$phraseTranslationKeys->expects($this->once())->method('addTag')->with('nuff', 'newContent')->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseTranslations', 'getPhraseTranslationKeys'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->exactly(2))->method('getPhraseTranslations')->with()->willReturn($phraseTranslations);
		$sync->expects($this->once())->method('getPhraseTranslationKeys')->with()->willReturn($phraseTranslationKeys);

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}
		$sync->setPreferDirection(Synchronize::PREFER_REMOTE)->setTagForContentChangeFromLocalToRemote('newContent');

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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseTranslations', 'getPhraseTranslationKeys'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->exactly(2))->method('getPhraseTranslations')->with()->willReturn($phraseTranslations);
		$sync->expects($this->never())->method('getPhraseTranslationKeys');

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}
		$sync->setPreferDirection(Synchronize::PREFER_REMOTE);

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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseTranslations'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->exactly(8))->method('getPhraseTranslations')->with()->willReturn($phraseTranslations);

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}
		$sync->setPreferDirection(Synchronize::PREFER_LOCAL);

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

		$phraseTranslationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['addTag'])->disableOriginalConstructor()->getMock();
		$phraseTranslationKeys->expects($this->once())->method('addTag')->with('nuff', 'newContent')->willReturn(false);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseTranslations', 'getPhraseTranslationKeys'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->once())->method('getPhraseTranslations')->with()->willReturn($phraseTranslations);
		$sync->expects($this->once())->method('getPhraseTranslationKeys')->with()->willReturn($phraseTranslationKeys);

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}
		$sync->setPreferDirection(Synchronize::PREFER_REMOTE)->setTagForContentChangeFromLocalToRemote('newContent');

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

		$phraseTranslationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['addTag'])->disableOriginalConstructor()->getMock();
		$phraseTranslationKeys->expects($this->once())->method('addTag')->with('nuff', 'newContent')->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getPhraseTranslations', 'getPhraseTranslationKeys'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->exactly(2))->method('getPhraseTranslations')->with()->willReturn($phraseTranslations);
		$sync->expects($this->once())->method('getPhraseTranslationKeys')->with()->willReturn($phraseTranslationKeys);

		foreach ($local as $locale => $keys)
		{
			$sync->addTranslations($locale, $keys);
		}
		$sync->setPreferDirection(Synchronize::PREFER_REMOTE)->setTagForContentChangeFromLocalToRemote('newContent');

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

		$phraseTranslationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseTranslationKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseTranslationKeys->expects($this->exactly(2))->method('create')->withConsecutive(['en'], ['fr'])->willReturn(true);
		$phraseTranslationKeys->expects($this->exactly(2))->method('delete')->withConsecutive(['nuff'], ['narf'])->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseTranslationKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(5))->method('getPhraseTranslationKeys')->with()->willReturn($phraseTranslationKeys);
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

		$phraseTranslationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseTranslationKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseTranslationKeys->expects($this->never())->method('create');
		$phraseTranslationKeys->expects($this->exactly(2))->method('delete')->withConsecutive(['nuff'], ['narf'])->willReturn(true);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseTranslationKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(3))->method('getPhraseTranslationKeys')->with()->willReturn($phraseTranslationKeys);
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

		$phraseTranslationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseTranslationKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseTranslationKeys->expects($this->exactly(2))->method('create')->withConsecutive(['en'], ['fr'])->willReturn(true);
		$phraseTranslationKeys->expects($this->never())->method('delete');

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseTranslationKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(3))->method('getPhraseTranslationKeys')->with()->willReturn($phraseTranslationKeys);
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

		$phraseTranslationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseTranslationKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseTranslationKeys->expects($this->never())->method('create');
		$phraseTranslationKeys->expects($this->never())->method('delete');

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseTranslationKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->once())->method('getPhraseTranslationKeys')->with()->willReturn($phraseTranslationKeys);
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

		$phraseTranslationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseTranslationKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseTranslationKeys->expects($this->once())->method('create')->with('en')->willReturn(false);
		$phraseTranslationKeys->expects($this->never())->method('delete');

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseTranslationKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(2))->method('getPhraseTranslationKeys')->with()->willReturn($phraseTranslationKeys);
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

		$phraseTranslationKeys = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetch', 'create', 'delete'])->disableOriginalConstructor()->getMock();
		$phraseTranslationKeys->expects($this->once())->method('fetch')->with()->willReturn($keysRemote);
		$phraseTranslationKeys->expects($this->exactly(2))->method('create')->withConsecutive(['en'], ['fr'])->willReturn(true);
		$phraseTranslationKeys->expects($this->once())->method('delete')->with('nuff')->willReturn(false);

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslations', 'getPhraseTranslationKeys', 'removeTranslationKeyFromAllLocales'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->once())->method('getTranslations')->with()->willReturn($keysLocal);
		$sync->expects($this->exactly(4))->method('getPhraseTranslationKeys')->with()->willReturn($phraseTranslationKeys);
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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslationLocales', 'getPhraseLocales', 'getTranslations'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->once())->method('getTranslationLocales')->with()->willReturn($localesLocale);
		$sync->expects($this->exactly(3))->method('getPhraseLocales')->with()->willReturn($phraseLocales);
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

		$sync = $this->getMockBuilder(Synchronize::class)->setMethods(['getTranslationLocales', 'getPhraseLocales', 'getTranslations'])->setConstructorArgs([$this->logger, 'a', 'b', 'c', 'd', 'de'])->getMock();
		$sync->expects($this->once())->method('getTranslationLocales')->with()->willReturn($localesLocale);
		$sync->expects($this->exactly(2))->method('getPhraseLocales')->with()->willReturn($phraseLocales);
		$sync->expects($this->never())->method('getTranslations');

		$reflectionMethod = new \ReflectionMethod($sync, 'synchronizeLocales');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($sync, 'translations');
		$reflectionProperty->setAccessible(true);

		$this->setExpectedException(FailureAddLocale::class);
		$reflectionMethod->invoke($sync);
		$this->assertEquals([], $reflectionProperty->getValue($sync));
	}
}
