<?php
namespace DasRedTest\PhraseApp\Command;

use DasRed\PhraseApp\Command\Factory;
use DasRed\PhraseApp\Command\Executor\Locale;
use DasRed\PhraseApp\Command\Executor\Key;
use DasRed\PhraseApp\Command\Executor\Translation;
use DasRed\PhraseApp\Config;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\PhraseApp\Command\Exception\InvalidCommand;
use DasRed\PhraseApp\Command\Exception\InvalidCommandOperation;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{

	protected $config;

	protected $console;

	public function setUp()
	{
		parent::setUp();

		$this->config = new Config('a', 'c', 'd');
		$this->config->setBaseUrl('e');

		$this->console = $this->getMockBuilder(AdapterInterface::class)->setMethods(['write', 'writeLine'])->getMockForAbstractClass();
	}

	public function tearDown()
	{
		$this->config = null;
		$this->console = null;
	}

	/**
	 * @covers ::__construct
	 */
	public function test__construct()
	{
		$factory = new Factory($this->config, $this->console);

		$this->assertSame($this->config, $factory->getConfig());
		$this->assertSame($this->console, $factory->getConsole());
	}

	public function dataProviderFactorySuccess()
	{
		return [
			[Locale\Create::class, ['locale', 'create', 'de-DE']],
			[Locale\Fetch::class, ['locale', 'list']],

			[Key\AddTag::class, ['key', 'addTag', 'nuff', 'narf']],
			[Key\Create::class, ['key', 'create', 'nuff', 'description', 'tag1', 'tag2', 'tag3', 'tag1', 'tag2', 'tag3']],
			[Key\Delete::class, ['key', 'delete', 'key']],
			[Key\Fetch::class, ['key', 'list']],
			[Key\Update::class, ['key', 'update', 'key', 'name', 'description', 'tag1', 'tag2', 'tag3']],

			[Translation\Store::class, ['translation', 'store', 'locale', 'key', 'content']],
		];
	}

	/**
	 * @covers ::factory
	 * @dataProvider dataProviderFactorySuccess
	 */
	public function testFactorySuccess($instance, $arguments)
	{
		$factory = new Factory($this->config, $this->console);

		$exec = $factory->factory($arguments);

		$this->assertInstanceOf($instance, $exec);
		$this->assertSame($this->config, $exec->getConfig());
		$this->assertSame($this->console, $exec->getConsole());
		$this->assertEquals(array_slice($arguments, 2), $exec->getArguments());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryFailedByCommand()
	{
		$factory = new Factory($this->config, $this->console);

		$this->setExpectedException(InvalidCommand::class);
		$exec = $factory->factory(['jkgfvneswalkfnde', 'gfnjedsaonfvcda']);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryFailedByOperation()
	{
		$factory = new Factory($this->config, $this->console);

		$this->setExpectedException(InvalidCommandOperation::class);
		$exec = $factory->factory(['locale', 'gfnjedsaonfvcda']);
	}
}
