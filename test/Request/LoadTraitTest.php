<?php
namespace DasRedTest\PhraseApp\Request;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Request\LoadTrait
 */
class LoadTraitTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers ::getMaxPerPage
	 */
	public function testGetMaxPerPage()
	{
		$load = $this->getMockBuilder('\\DasRed\\PhraseApp\\Request\\LoadTrait')->setMethods([])->getMockForTrait();

		$reflectionMethod = new \ReflectionMethod($load, 'getMaxPerPage');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(100, $reflectionMethod->invoke($load));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadSuccess()
	{
		$load = $this->getMockBuilder('\\DasRed\\PhraseApp\\Request\\LoadTrait')->setMethods(['getMaxPerPage', 'getUrlApi', 'methodGet'])->getMockForTrait();
		$load->expects($this->once())->method('getMaxPerPage')->with()->willReturn(4);
		$load->expects($this->exactly(4))->method('getUrlApi')->with()->willReturn('nuff/a/b');
		$load->expects($this->exactly(4))->method('methodGet')->withConsecutive(
			['nuff/a/b', ['page' => 1, 'per_page' => 4]],
			['nuff/a/b', ['page' => 2, 'per_page' => 4]],
			['nuff/a/b', ['page' => 3, 'per_page' => 4]],
			['nuff/a/b', ['page' => 4, 'per_page' => 4]]
		)->willReturnOnConsecutiveCalls(
			['a', 'b', 'c', 'd'],
			['A', 'B', 'C', 'D'],
			[1, 2, 3, 4],
			[5, 6, 7]
		);

		$reflectionMethod = new \ReflectionMethod($load, 'load');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([
			'a', 'b', 'c', 'd',
			'A', 'B', 'C', 'D',
			1, 2, 3, 4,
			5, 6, 7
		], $reflectionMethod->invoke($load));
	}
}