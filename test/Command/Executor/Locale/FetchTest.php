<?php
namespace DasRedTest\PhraseApp\Command\Executor\Locale;

use DasRed\PhraseApp\Command\Executor\Locale\Fetch;
use DasRed\PhraseApp\Config;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\PhraseApp\Request\Locales;
use Zend\Console\ColorInterface;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Executor\Locale\Fetch
 */
class FetchTest extends \PHPUnit_Framework_TestCase
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
	 * @covers ::execute
	 */
	public function testExecute()
	{
		$requester = $this->getMockBuilder(Locales::class)->setMethods(['load'])->setConstructorArgs([$this->config])->getMock();
		$requester->expects($this->once())->method('load')->with()->willReturn([
			'a' => ['name' => 'AA', 'code' => 'aa'],
			'b' => ['name' => 'BB', 'code' => 'bb'],
			'c' => ['name' => 'CC', 'code' => 'cc'],
			'd' => ['name' => 'DD', 'code' => 'dd'],
		]);

		$exec = new Fetch($this->config, $this->console, []);
		$exec->setPhraseAppLocales($requester);

		$this->console->expects($this->exactly(10))->method('write')->withConsecutive(
			['Found '],
			[4, ColorInterface::LIGHT_GREEN],
			[' - '],
			['aa', ColorInterface::LIGHT_GREEN],
			[' - '],
			['bb', ColorInterface::LIGHT_GREEN],
			[' - '],
			['cc', ColorInterface::LIGHT_GREEN],
			[' - '],
			['dd', ColorInterface::LIGHT_GREEN]
		);
		$this->console->expects($this->exactly(5))->method('writeLine')->withConsecutive(
			[' locales.'],
			[' AA'],
			[' BB'],
			[' CC'],
			[' DD']
		);

		$this->assertTrue($exec->execute());
	}

	public function dataProviderValidateArguments()
	{
		return [
			[['a', 'b'], false],
			[['', 'b'], false],
			[['a', ''], false],
			[[null, 'b'], false],
			[['b', null], false],
			[[], true],
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
		$exec = new Fetch($this->config, $this->console, []);

		$reflectionMethod = new \ReflectionMethod($exec, 'validateArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($expected, $reflectionMethod->invoke($exec, $arguments));
	}
}