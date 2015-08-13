<?php
namespace DasRedTest\PhraseApp\Command\Executor\Translation;

use DasRed\PhraseApp\Command\Executor\Translation\Store;
use DasRed\PhraseApp\Config;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\PhraseApp\Request\Translations;
use Zend\Console\ColorInterface;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Executor\Translation\Store
 */
class StoreTest extends \PHPUnit_Framework_TestCase
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
			[['abc', 'def', 'ghi'], true],
			[['abc', 'def', 'ghi'], false],
		];
	}

	/**
	 * @covers ::execute
	 * @dataProvider dataProviderExecute
	 */
	public function testExecute($arguments, $expected)
	{
		$requester = $this->getMockBuilder(Translations::class)->setMethods(['store'])->setConstructorArgs([$this->config])->getMock();
		$requester->expects($this->once())->method('store')->with($arguments[0], $arguments[1], $arguments[2])->willReturn($expected);

		$exec = new Store($this->config, $this->console, $arguments);
		$exec->setPhraseAppTranslations($requester);

		$this->console->expects($this->never())->method('write');
		if ($expected === true)
		{
			$this->console->expects($this->once())->method('writeLine')->with('Content was setted to locale ' . $arguments[0] . ' for the key ' . $arguments[1] . '.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);
		}
		else
		{
			$this->console->expects($this->once())->method('writeLine')->with('Content can not be setted to locale ' . $arguments[0] . ' for the key ' . $arguments[1] . '.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
		}

		$this->assertSame($expected, $exec->execute());
	}

	public function dataProviderValidateArguments()
	{
		return [
			[['a', 'b', 'c'], true],
			[['a', 'b', ''], false],
			[['a', 'b'], false],
			[['', 'b'], false],
			[['a', ''], false],
			[[null, 'b'], false],
			[['b', null], false],
			[[], false],
			[[1], false],
			[[1, 2, 3, 4, 45], false],
		];
	}

	/**
	 * @covers ::validateArguments
	 * @dataProvider dataProviderValidateArguments
	 */
	public function testValidateArguments($arguments, $expected)
	{
		$exec = new Store($this->config, $this->console, [1, 2, 3]);

		$reflectionMethod = new \ReflectionMethod($exec, 'validateArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($expected, $reflectionMethod->invoke($exec, $arguments));
	}
}