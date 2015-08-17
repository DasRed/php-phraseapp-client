<?php
namespace DasRedTest\PhraseApp\Command\Executor\Locale;

use DasRed\PhraseApp\Command\Executor\Locale\Create;
use DasRed\PhraseApp\Config;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\PhraseApp\Request\Locales;
use Zend\Console\ColorInterface;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Executor\Locale\Create
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
			[['abc', 'de'], true],
			[['abc'], true],
			[['abc'], false],
			[['abc', 'de'], false],
		];
	}

	/**
	 * @covers ::execute
	 * @dataProvider dataProviderExecute
	 */
	public function testExecute($arguments, $expected)
	{
		$temp = $arguments;
		$locale = array_shift($temp);
		$localeSource = array_shift($temp);

		$requester = $this->getMockBuilder(Locales::class)->setMethods(['create'])->setConstructorArgs([$this->config])->getMock();
		$requester->expects($this->once())->method('create')->with($locale, $localeSource)->willReturn($expected);

		$exec = new Create($this->config, $this->console, $arguments);
		$exec->setPhraseAppLocales($requester);

		$this->console->expects($this->never())->method('write');
		if ($expected === true)
		{
			$this->console->expects($this->once())->method('writeLine')->with('Locale ' . $locale . ' created.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);
		}
		else
		{
			$this->console->expects($this->once())->method('writeLine')->with('Locale ' . $locale . ' can not be created.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
		}

		$this->assertSame($expected, $exec->execute());
	}

	public function dataProviderValidateArguments()
	{
		return [
			[['a', 'b'], true],
			[['', 'b'], false],
			[['a', ''], false],
			[['a'], true],
			[[''], false],
			[[null, 'b'], false],
			[['b', null], false],
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
		$exec = new Create($this->config, $this->console, [1]);

		$reflectionMethod = new \ReflectionMethod($exec, 'validateArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($expected, $reflectionMethod->invoke($exec, $arguments));
	}
}