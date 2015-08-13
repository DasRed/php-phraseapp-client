<?php
namespace DasRedTest\PhraseApp\Command\Executor\Key;

use DasRed\PhraseApp\Command\Executor\Key\Fetch;
use DasRed\PhraseApp\Config;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\PhraseApp\Request\Keys;
use Zend\Console\ColorInterface;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Command\Executor\Key\Fetch
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
		$requester = $this->getMockBuilder(Keys::class)->setMethods(['load'])->setConstructorArgs([$this->config])->getMock();
		$requester->expects($this->once())->method('load')->with()->willReturn([
			'a' => ['id' => 1, 'name' => 'a', 'description' => 'aa', 'tags' => ['t.a', 't.aa']],
			'b' => ['id' => 2, 'name' => 'b', 'description' => 'bb', 'tags' => ['t.b', 't.bb']],
			'c' => ['id' => 3, 'name' => 'c', 'description' => 'cc', 'tags' => ['t.c', 't.c']],
			'd' => ['id' => 4, 'name' => 'd', 'description' => 'dd', 'tags' => ['t.d', 't.dd']],
		]);

		$exec = new Fetch($this->config, $this->console, []);
		$exec->setPhraseAppKeys($requester);

		$this->console->expects($this->exactly(10))->method('write')->withConsecutive(
			['Found '],
			[4, ColorInterface::LIGHT_GREEN],
			[' - '],
			['a', ColorInterface::LIGHT_GREEN],
			[' - '],
			['b', ColorInterface::LIGHT_GREEN],
			[' - '],
			['c', ColorInterface::LIGHT_GREEN],
			[' - '],
			['d', ColorInterface::LIGHT_GREEN]
		);
		$this->console->expects($this->exactly(5))->method('writeLine')->withConsecutive(
			[' keys.'],
			['       aa'],
			['       bb'],
			['       cc'],
			['       dd']
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