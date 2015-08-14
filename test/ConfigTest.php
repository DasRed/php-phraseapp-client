<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\Config\Exception\InvalidPreferDirection;
use DasRed\PhraseApp\Config\Exception\AccessTokenCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception\ApplicationNameCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception\BaseUrlCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception\LocaleDefaultCanNotBeEmpty;
use DasRed\PhraseApp\Config\Exception\ProjectIdCanNotBeEmpty;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
	public function	dataProviderEmpty()
	{
		return [
			[null],
			[''],
			['0'],
			[0],
			[0.0],
			[false],
			[[]],
		];
	}

	/**
	 * @covers ::__construct
	 */
	public function test__construct()
	{
		$config = new Config('pp', 'a', 'de');

		$this->assertSame('pp', $config->getProjectId());
		$this->assertSame('a', $config->getAccessToken());
		$this->assertSame('de', $config->getLocaleDefault());
	}

	/**
	 * @covers ::getAccessToken
	 * @covers ::setAccessToken
	 */
	public function testGetSetAccessToken()
	{
		$config = new Config('pp', 'a', 'de');

		$this->assertSame('a', $config->getAccessToken());
		$this->assertSame($config, $config->setAccessToken('b'));
		$this->assertSame('b', $config->getAccessToken());
	}

	/**
	 * @covers ::setAccessToken
	 * @dataProvider dataProviderEmpty
	 */
	public function testSetAccessToken($value)
	{
		$config = new Config('pp', 'a', 'de');

		$this->setExpectedException(AccessTokenCanNotBeEmpty::class);
		$config->setAccessToken($value);
	}

	/**
	 * @covers ::getApplicationName
	 * @covers ::setApplicationName
	 */
	public function testGetSetApplicationName()
	{
		$config = new Config('pp', 'a', 'de');

		$this->assertSame('PHP PhraseApp Client (https://github.com/DasRed/php-phraseapp-client)', $config->getApplicationName());
		$this->assertSame($config, $config->setApplicationName('b'));
		$this->assertSame('b', $config->getApplicationName());
	}

	/**
	 * @covers ::setApplicationName
	 * @dataProvider dataProviderEmpty
	 */
	public function testSetApplicationName($value)
	{
		$config = new Config('pp', 'a', 'de');

		$this->setExpectedException(ApplicationNameCanNotBeEmpty::class);
		$config->setApplicationName($value);
	}

	/**
	 * @covers ::getBaseUrl
	 * @covers ::setBaseUrl
	 */
	public function testGetSetBaseUrl()
	{
		$config = new Config('pp', 'a', 'de');

		$this->assertSame('https://api.phraseapp.com/api/v2/', $config->getBaseUrl());
		$this->assertSame($config, $config->setBaseUrl('b'));
		$this->assertSame('b/', $config->getBaseUrl());
	}

	/**
	 * @covers ::setBaseUrl
	 * @dataProvider dataProviderEmpty
	 */
	public function testSetBaseUrl($value)
	{
		$config = new Config('pp', 'a', 'de');

		$this->setExpectedException(BaseUrlCanNotBeEmpty::class);
		$config->setBaseUrl($value);
	}

	/**
	 * @covers ::getLocaleDefault
	 * @covers ::setLocaleDefault
	 */
	public function testGetSetLocaleDefault()
	{
		$config = new Config('pp', 'a', 'de');

		$this->assertSame('de', $config->getLocaleDefault());
		$this->assertSame($config, $config->setLocaleDefault('b'));
		$this->assertSame('b', $config->getLocaleDefault());
	}

	/**
	 * @covers ::setLocaleDefault
	 * @dataProvider dataProviderEmpty
	 */
	public function testSetLocaleDefault($value)
	{
		$config = new Config('pp', 'a', 'de');

		$this->setExpectedException(LocaleDefaultCanNotBeEmpty::class);
		$config->setLocaleDefault($value);
	}

	/**
	 * @covers ::getPreferDirection
	 * @covers ::setPreferDirection
	 */
	public function testGetSetPreferDirectionSuccess()
	{
		$config = new Config('pp', 'a', 'de');

		$this->assertSame(Config::PREFER_REMOTE, $config->getPreferDirection());
		$this->assertSame($config, $config->setPreferDirection(Config::PREFER_LOCAL));
		$this->assertSame(Config::PREFER_LOCAL, $config->getPreferDirection());
		$this->assertSame($config, $config->setPreferDirection(Config::PREFER_REMOTE));
		$this->assertSame(Config::PREFER_REMOTE, $config->getPreferDirection());
	}

	/**
	 * @covers ::setPreferDirection
	 */
	public function testSetPreferDirection()
	{
		$config = new Config('pp', 'a', 'de');

		$this->setExpectedException(InvalidPreferDirection::class);
		$config->setPreferDirection('nuff');
	}

	/**
	 * @covers ::getTagForContentChangeFromLocalToRemote
	 * @covers ::setTagForContentChangeFromLocalToRemote
	 */
	public function testGetSetTagForContentChangeFromLocalToRemote()
	{
		$config = new Config('pp', 'a', 'de');

		$this->assertSame('newContent', $config->getTagForContentChangeFromLocalToRemote());
		$this->assertSame($config, $config->setTagForContentChangeFromLocalToRemote('nuff'));
		$this->assertSame('nuff', $config->getTagForContentChangeFromLocalToRemote());
		$this->assertSame($config, $config->setTagForContentChangeFromLocalToRemote('narf'));
		$this->assertSame('narf', $config->getTagForContentChangeFromLocalToRemote());
		$this->assertSame($config, $config->setTagForContentChangeFromLocalToRemote(null));
		$this->assertNull($config->getTagForContentChangeFromLocalToRemote());
	}

	/**
	 * @covers ::getProjectId
	 * @covers ::setProjectId
	 */
	public function testGetSetProjectId()
	{
		$config = new Config('pp', 'a', 'de');

		$this->assertSame('pp', $config->getProjectId());
		$this->assertSame($config, $config->setProjectId('b'));
		$this->assertSame('b', $config->getProjectId());
	}

	/**
	 * @covers ::setProjectId
	 * @dataProvider dataProviderEmpty
	 */
	public function testSetProjectId($value)
	{
		$config = new Config('pp', 'a', 'de');

		$this->setExpectedException(ProjectIdCanNotBeEmpty::class);
		$config->setProjectId($value);
	}

	/**
	 * @covers ::getUseLocaleDefaultAsLocaleSource
	 * @covers ::setUseLocaleDefaultAsLocaleSource
	 */
	public function testGetSetUseLocaleDefaultAsLocaleSource()
	{
		$config = new Config('pp', 'a', 'de');

		$this->assertTrue($config->getUseLocaleDefaultAsLocaleSource());
		$this->assertSame($config, $config->setUseLocaleDefaultAsLocaleSource(false));
		$this->assertFalse($config->getUseLocaleDefaultAsLocaleSource());
		$this->assertSame($config, $config->setUseLocaleDefaultAsLocaleSource(true));
		$this->assertTrue($config->getUseLocaleDefaultAsLocaleSource());
	}
}