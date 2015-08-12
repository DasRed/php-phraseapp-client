<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\Config\Exception\InvalidPreferDirection;

/**
 * @coversDefault \DasRed\PhraseApp\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::__construct()
	 */
	public function test__construct()
	{
		$config = new Config('a', 'de', 'b', 'c');

		$this->assertSame('a', $config->getAccessToken());
		$this->assertSame('de', $config->getLocaleDefault());
		$this->assertSame('b', $config->getApplicationName());
		$this->assertSame('c/', $config->getBaseUrl());
	}

	/**
	 * @covers ::getAccessToken()
	 * @covers ::setAccessToken()
	 */
	public function testGetSetAccessToken()
	{
		$config = new Config('a', 'de');

		$this->assertSame('a', $config->getAccessToken());
		$this->assertSame($config, $config->setAccessToken('b'));
		$this->assertSame('b', $config->getAccessToken());
	}

	/**
	 * @covers ::getApplicationName()
	 * @covers ::setApplicationName()
	 */
	public function testGetSetApplicationName()
	{
		$config = new Config('a', 'de');

		$this->assertSame('PHP PhraseApp Client (https://github.com/DasRed/php-phraseapp-client)', $config->getApplicationName());
		$this->assertSame($config, $config->setApplicationName('b'));
		$this->assertSame('b', $config->getApplicationName());
	}

	/**
	 * @covers ::getBaseUrl()
	 * @covers ::setBaseUrl()
	 */
	public function testGetSetBaseUrl()
	{
		$config = new Config('a', 'de');

		$this->assertSame('https://api.phraseapp.com/api/v2/', $config->getBaseUrl());
		$this->assertSame($config, $config->setBaseUrl('b'));
		$this->assertSame('b/', $config->getBaseUrl());
	}

	/**
	 * @covers ::getLocaleDefault()
	 * @covers ::setLocaleDefault()
	 */
	public function testGetSetLocaleDefault()
	{
		$config = new Config('a', 'de');

		$this->assertSame('de', $config->getLocaleDefault());
		$this->assertSame($config, $config->setLocaleDefault('b'));
		$this->assertSame('b', $config->getLocaleDefault());
	}

	/**
	 * @covers ::getPreferDirection
	 * @covers ::setPreferDirection
	 */
	public function testGetSetPreferDirectionSuccess()
	{
		$config = new Config('a', 'de');

		$this->assertSame(Config::PREFER_REMOTE, $config->getPreferDirection());
		$this->assertSame($config, $config->setPreferDirection(Config::PREFER_LOCAL));
		$this->assertSame(Config::PREFER_LOCAL, $config->getPreferDirection());
		$this->assertSame($config, $config->setPreferDirection(Config::PREFER_REMOTE));
		$this->assertSame(Config::PREFER_REMOTE, $config->getPreferDirection());
	}

	/**
	 * @covers ::getPreferDirection
	 * @covers ::setPreferDirection
	 */
	public function testGetSetPreferDirectionFailed()
	{
		$config = new Config('a', 'de');

		$this->setExpectedException(InvalidPreferDirection::class);
		$config->setPreferDirection('nuff');
	}

	/**
	 * @covers ::getTagForContentChangeFromLocalToRemote
	 * @covers ::setTagForContentChangeFromLocalToRemote
	 */
	public function testGetSetTagForContentChangeFromLocalToRemote()
	{
		$config = new Config('a', 'de');

		$this->assertNull($config->getTagForContentChangeFromLocalToRemote());
		$this->assertSame($config, $config->setTagForContentChangeFromLocalToRemote('nuff'));
		$this->assertSame('nuff', $config->getTagForContentChangeFromLocalToRemote());
		$this->assertSame($config, $config->setTagForContentChangeFromLocalToRemote('narf'));
		$this->assertSame('narf', $config->getTagForContentChangeFromLocalToRemote());
	}
}