<?php
namespace DasRedTest\PhraseApp\Command;

use DasRed\PhraseApp\Command\ExecutorAbstract;
use DasRed\PhraseApp\Config;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\PhraseApp\Command\Exception\InvalidArguments;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\ExecutorAbstract
 */
class ExecutorAbstractTest extends \PHPUnit_Framework_TestCase
{

	protected $config;

	protected $console;

	protected $arguments;

	public function setUp()
	{
		parent::setUp();

		$this->config = new Config('a', 'c', 'd');
		$this->config->setBaseUrl('e');

		$this->console = $this->getMockBuilder(AdapterInterface::class)->setMethods(['write', 'writeLine'])->getMockForAbstractClass();

		$this->arguments = [1, 2, 3];
	}

	public function tearDown()
	{
		$this->config = null;
		$this->console = null;
		$this->arguments = null;
	}

	/**
	 * @covers ::__construct
	 */
	public function test__construct()
	{
		$exec = $this->getMockBuilder(ExecutorAbstract::class)->setMethods([])->setConstructorArgs([$this->config, $this->console, $this->arguments])->getMockForAbstractClass();

		$this->assertSame($this->config, $exec->getConfig());
		$this->assertSame($this->console, $exec->getConsole());
		$this->assertEquals($this->arguments, $exec->getArguments());
	}

	/**
	 * @covers ::getArguments
	 * @covers ::setArguments
	 */
	public function testGetSetArguments()
	{
		$arguments = [564, 4654, 64];
		$exec = $this->getMockBuilder(ExecutorAbstract::class)->setMethods([])->setConstructorArgs([$this->config, $this->console, $this->arguments])->getMockForAbstractClass();

		$this->assertEquals($this->arguments, $exec->getArguments());
		$this->assertSame($exec, $exec->setArguments($arguments));
		$this->assertEquals($arguments, $exec->getArguments());
	}

	/**
	 * @covers ::setArguments
	 */
	public function testSetArguments()
	{
		$exec = $this->getMockBuilder(ExecutorAbstract::class)->setMethods([])->setConstructorArgs([$this->config, $this->console, $this->arguments])->getMockForAbstractClass();

		$this->setExpectedException(InvalidArguments::class);
		$exec->setArguments(['', null]);
	}

	public function dataProviderValidateArguments()
	{
		return [
			[['a', 'b'], true],
			[['', 'b'], false],
			[['a', ''], false],
			[[null, 'b'], false],
			[['b', null], false],
			[[], true],
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
		$exec = $this->getMockBuilder(ExecutorAbstract::class)->setMethods([])->setConstructorArgs([$this->config, $this->console, $this->arguments])->getMockForAbstractClass();

		$reflectionMethod = new \ReflectionMethod($exec, 'validateArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($expected, $reflectionMethod->invoke($exec, $arguments));
	}
}
