<?php
namespace DasRedTest\PhraseApp\Collection;

use DasRed\PhraseApp\Collection\SubArray;

/**
 * @coversDefaultClass \DasRed\PhraseApp\Collection\SubArray
 */
class SubArrayTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function test__construct()
	{
		$collection = $this->getMockBuilder(SubArray::class)->setMethods(['append'])->setConstructorArgs(['idd'])->getMockForAbstractClass();
		$collection->expects($this->exactly(4))->method('append')->withConsecutive([1],[2],[3],[4]);

		$collection->__construct('id', [1,2,3,4]);

		$this->assertSame('id', $collection->getIdKey());
	}

	/**
	 * @covers ::append
	 */
	public function testAppend()
	{
		$collection = new SubArray('id');

		$this->assertNull($collection->append(['id' => 'A', 'nuff' => 'narf']));
		$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $collection->get('A'));

		$this->assertNull($collection->append(['id' => 'B', 'narf' => 'nuff']));
		$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $collection->get('A'));
		$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $collection->get('B'));

		$this->assertNull($collection->append(['id' => 'A', 'narf' => 'nuff']));
		$this->assertEquals(['id' => 'A', 'narf' => 'nuff'], $collection->get('A'));
		$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $collection->get('B'));
	}

	/**
	 * @covers ::combine
	 */
	public function testCombine()
	{
		$collection = new SubArray('id');

		$this->assertSame($collection, $collection->combine([
			['id' => 'A', 'nuff' => 'narf'],
			['id' => 'B', 'narf' => 'nuff'],
			['id' => 'C', 'lol' => 'nuff']
		]));

		$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $collection->get('A'));
		$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $collection->get('B'));
		$this->assertEquals(['id' => 'C', 'lol' => 'nuff'], $collection->get('C'));
	}

	/**
	 * @covers ::find
	 */
	public function testFind()
	{
		$collection = new SubArray('id');

		$this->assertNull($collection->find(function($entry)
		{
			$this->fail();
		}));

		$collection->append(['id' => 'A', 'nuff' => 'narf']);
		$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $collection->find(function($entry, $index)
		{
			$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $entry);
			$this->assertEquals('A', $index);

			return true;
		}));

		$collection->append(['id' => 'B', 'narf' => 'nuff']);
		$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $collection->find(function($entry, $index)
		{
			static $i = 0;

			if ($i == 0)
			{
				$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $entry);
				$this->assertEquals('A', $index);
			}
			elseif ($i == 1)
			{
				$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $entry);
				$this->assertEquals('B', $index);
			}
			else
			{
				$this->fail();
			}

			$i++;

			return $i == 2;
		}));

		$collection->append(['id' => 'C', 'lol' => 'nuff']);
		$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $collection->find(function($entry, $index)
		{
			static $i = 0;

			if ($i == 0)
			{
				$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $entry);
				$this->assertEquals('A', $index);
			}
			elseif ($i == 1)
			{
				$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $entry);
				$this->assertEquals('B', $index);
			}
			else
			{
				$this->fail();
			}

			$i++;

			return $i == 2;
		}));
	}

	/**
	 * @covers ::get
	 */
	public function testGet()
	{
		$collection = new SubArray('id');

		$this->assertNull($collection->get('A'));
		$collection->append(['id' => 'A', 'nuff' => 'narf']);
		$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $collection->get('A'));
	}

	/**
	 * @covers ::keys
	 */
	public function testKeys()
	{
		$collection = new SubArray('id');

		$this->assertEquals([], $collection->keys());

		$collection->combine([
			['id' => 'A', 'nuff' => 'narf'],
			['id' => 'B', 'narf' => 'nuff'],
			['id' => 'C', 'lol' => 'nuff']
		]);

		$this->assertEquals(['A', 'B', 'C'], $collection->keys());
	}

	/**
	 * @covers ::getIdKey
	 * @covers ::setIdKey
	 */
	public function testGetSetBaseUrl()
	{
		$collection = new SubArray('id');

		$reflectionMethod = new \ReflectionMethod($collection, 'setIdKey');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('id', $collection->getIdKey());
		$this->assertSame($collection, $reflectionMethod->invoke($collection, 'b'));
		$this->assertSame('b', $collection->getIdKey());
	}

	/**
	 * @covers ::map
	 */
	public function testMap()
	{
		$collection = new SubArray('id');

		$this->assertEquals([], $collection->map(function($entry)
		{
			$this->fail();
		}));

		$collection->append(['id' => 'A', 'nuff' => 'narf']);
		$this->assertEquals(['A' => 'rofl'], $collection->map(function($entry, $index)
		{
			$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $entry);
			$this->assertEquals('A', $index);

			return 'rofl';
		}));

		$collection->append(['id' => 'B', 'narf' => 'nuff']);
		$this->assertEquals(['A' => 'noop', 'B' => 'lol'], $collection->map(function($entry, $index)
		{
			static $i = 0;

			if ($i == 0)
			{
				$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $entry);
				$this->assertEquals('A', $index);
				$return = 'noop';
			}
			elseif ($i == 1)
			{
				$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $entry);
				$this->assertEquals('B', $index);
				$return = 'lol';
			}
			else
			{
				$this->fail();
			}

			$i++;

			return $return;
		}));

		$collection->append(['id' => 'C', 'lol' => 'nuff']);
		$this->assertEquals(['A' => 'noop', 'B' => 'lol', 'C' => 'jup'], $collection->map(function($entry, $index)
		{
			static $i = 0;

			if ($i == 0)
			{
				$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $entry);
				$this->assertEquals('A', $index);
				$return = 'noop';
			}
			elseif ($i == 1)
			{
				$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $entry);
				$this->assertEquals('B', $index);
				$return = 'lol';
			}
			elseif ($i === 2)
			{
				$this->assertEquals(['id' => 'C', 'lol' => 'nuff'], $entry);
				$this->assertEquals('C', $index);
				$return = 'jup';
			}
			else
			{
				$this->fail();
			}

			$i++;

			return $return;
		}));
	}

	/**
	 * @covers ::remove
	 */
	public function testRemove()
	{
		$collection = new SubArray('id');

		$collection->combine([
			['id' => 'A', 'nuff' => 'narf'],
			['id' => 'B', 'narf' => 'nuff'],
			['id' => 'C', 'lol' => 'nuff']
		]);

		$this->assertSame($collection, $collection->remove('A'));
		$this->assertEquals(['B', 'C'], $collection->keys());
		$this->assertSame($collection, $collection->remove('A'));
		$this->assertEquals(['B', 'C'], $collection->keys());
	}

	/**
	 * @covers ::each
	 */
	public function testEach()
	{
		$collection = new SubArray('id');

		$this->assertSame($collection, $collection->each(function($entry)
		{
			$this->fail();
		}));

		$collection->append(['id' => 'A', 'nuff' => 'narf']);
		$this->assertSame($collection, $collection->each(function($entry, $index)
		{
			$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $entry);
			$this->assertEquals('A', $index);
		}));

		$collection->append(['id' => 'B', 'narf' => 'nuff']);
		$this->assertSame($collection, $collection->each(function($entry, $index)
		{
			static $i = 0;

			if ($i == 0)
			{
				$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $entry);
				$this->assertEquals('A', $index);
			}
			elseif ($i == 1)
			{
				$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $entry);
				$this->assertEquals('B', $index);
			}
			else
			{
				$this->fail();
			}

			$i++;
		}));

		$collection->append(['id' => 'C', 'lol' => 'nuff']);
		$this->assertSame($collection, $collection->each(function($entry, $index)
		{
			static $i = 0;

			if ($i == 0)
			{
				$this->assertEquals(['id' => 'A', 'nuff' => 'narf'], $entry);
				$this->assertEquals('A', $index);
			}
			elseif ($i == 1)
			{
				$this->assertEquals(['id' => 'B', 'narf' => 'nuff'], $entry);
				$this->assertEquals('B', $index);
			}
			elseif ($i === 2)
			{
				$this->assertEquals(['id' => 'C', 'lol' => 'nuff'], $entry);
				$this->assertEquals('C', $index);
			}
			else
			{
				$this->fail();
			}

			$i++;
		}));
	}
}