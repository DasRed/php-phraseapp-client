<?php
namespace DasRedTest\PhraseApp\Command\Executor\Key;

use DasRed\PhraseApp\Command\Executor\Key\Delete;
use DasRed\PhraseApp\Config;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\PhraseApp\Request\Keys;
use Zend\Console\ColorInterface;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Executor\Key\Delete
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
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
			[['abc'], false],
		];
	}

	/**
	 * @covers ::execute
	 * @dataProvider dataProviderExecute
	 */
	public function testExecute($arguments, $expected)
	{
		$requester = $this->getMockBuilder(Keys::class)->setMethods(['delete'])->setConstructorArgs([$this->config])->getMock();
		$requester->expects($this->once())->method('delete')->with($arguments[0])->willReturn($expected);

		$exec = new Delete($this->config, $this->console, $arguments);
		$exec->setPhraseAppKeys($requester);

		$this->console->expects($this->never())->method('write');
		if ($expected === true)
		{
			$this->console->expects($this->once())->method('writeLine')->with('Key ' . $arguments[0] . ' deleted.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);
		}
		else
		{
			$this->console->expects($this->once())->method('writeLine')->with('Key ' . $arguments[0] . ' can not be deleted.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
		}

		$this->assertSame($expected, $exec->execute());
	}

	public function dataProviderValidateArguments()
	{
		return [
			[['a', 'b'], false],
			[['', 'b'], false],
			[['a', ''], false],
			[[null, 'b'], false],
			[['b', null], false],
			[['b'], true],
			[[], false],
			[[1], true],
			[[1, 2, 3, 4, 45], false],
		];
	}

	/**
	 * @covers ::validateArguments
	 * @dataProvider dataProviderValidateArguments
	 */
	public function testValidateArguments($arguments, $expected)
	{
		$exec = new Delete($this->config, $this->console, [1]);

		$reflectionMethod = new \ReflectionMethod($exec, 'validateArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($expected, $reflectionMethod->invoke($exec, $arguments));
	}
}