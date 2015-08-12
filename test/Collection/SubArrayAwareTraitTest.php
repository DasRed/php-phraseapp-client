<?php
namespace DasRedTest\PhraseApp\Collection;

use DasRed\PhraseApp\Collection\SubArray;
/**
 * @coversDefaultClass \DasRed\PhraseApp\Collection\SubArrayAwareTrait
 */
class SubArrayAwareTraitTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::getCollection
	 */
	public function testGetCollectionWithoutData()
	{
		$trait = $this->getMockBuilder('\DasRed\PhraseApp\Collection\SubArrayAwareTrait')->setMethods(['getIdKey', 'load'])->getMockForTrait();
		$trait->expects($this->once())->method('getIdKey')->with()->willReturn('nuff');
		$trait->expects($this->once())->method('load')->with()->willReturn([]);

		$collection = $trait->getCollection();
		$this->assertInstanceOf(SubArray::class, $collection);
		$this->assertSame($collection, $trait->getCollection());
		$this->assertSame('nuff', $collection->getIdKey());
		$this->assertSame(0, $collection->count());
	}

	/**
	 * @covers ::getCollection
	 */
	public function testGetCollectionWithData()
	{
		$trait = $this->getMockBuilder('\DasRed\PhraseApp\Collection\SubArrayAwareTrait')->setMethods(['getIdKey', 'load'])->getMockForTrait();
		$trait->expects($this->once())->method('getIdKey')->with()->willReturn('nuff');
		$trait->expects($this->once())->method('load')->with()->willReturn([['nuff' => 'narf'], ['nuff' => 'rofl']]);

		$collection = $trait->getCollection();
		$this->assertInstanceOf(SubArray::class, $collection);
		$this->assertSame($collection, $trait->getCollection());
		$this->assertSame('nuff', $collection->getIdKey());
		$this->assertSame(2, $collection->count());
		$this->assertEquals(['narf' => ['nuff' => 'narf'], 'rofl' => ['nuff' => 'rofl']], $collection->getArrayCopy());
	}
}