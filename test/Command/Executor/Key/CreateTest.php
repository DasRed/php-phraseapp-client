<?php
namespace DasRedTest\PhraseApp\Command\Executor\Key;

use DasRed\PhraseApp\Command\Executor\Key\Create;
use DasRed\PhraseApp\Config;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\PhraseApp\Request\Keys;
use Zend\Console\ColorInterface;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Executor\Key\Create
 */
class CreateTest extends \PHPUnit_Framework_TestCase
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

	public function dataProviderExecute()
	{
		return [
			[['abc'], true],
			[['abc', 'def'], true],
			[['abc', 'def', 'b'], true],
			[['abc', 'def', 'b'], true],
			[['abc', 'def', 'b', 'c', 'd', 'e'], true],
			[['abc', 'def', 'b', 'c', 'd', 'e'], false],
		];
	}

	/**
	 * @covers ::execute
	 * @dataProvider dataProviderExecute
	 */
	public function testExecute($arguments, $expected)
	{
		$tags = $arguments;
		$name = array_shift($tags);
		$description = array_shift($tags);

		$requester = $this->getMockBuilder(Keys::class)->setMethods(['create'])->setConstructorArgs([$this->config])->getMock();
		$requester->expects($this->once())->method('create')->with($name, $description, $tags)->willReturn($expected);

		$exec = new Create($this->config, $this->console, $arguments);
		$exec->setPhraseAppKeys($requester);

		$this->console->expects($this->never())->method('write');
		if ($expected === true)
		{
			$this->console->expects($this->once())->method('writeLine')->with('Key ' . $name . ' created.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);
		}
		else
		{
			$this->console->expects($this->once())->method('writeLine')->with('Key ' . $name . ' can not be created.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
		}

		$this->assertSame($expected, $exec->execute());
	}

	public function dataProviderValidateArguments()
	{
		return [
			[['a', 'b'], true],
			[['', 'b'], false],
			[['a', ''], false],
			[[null, 'b'], false],
			[['b', null], false],
			[[], false],
			[[1], true],
			[[1, 2, 3, 4, 45], true],
		];
	}

	/**
	 * @covers ::validateArguments
	 * @dataProvider dataProviderValidateArguments
	 */
	public function testValidateArguments($arguments, $expected)
	{
		$exec = new Create($this->config, $this->console, [1, 2]);

		$reflectionMethod = new \ReflectionMethod($exec, 'validateArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($expected, $reflectionMethod->invoke($exec, $arguments));
	}
}