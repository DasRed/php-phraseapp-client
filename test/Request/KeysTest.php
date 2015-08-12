<?php
namespace DasRedTest\PhraseApp\Request;

use DasRed\PhraseApp\Request\Keys;
use DasRed\PhraseApp\Config;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request\Keys
 */
class KeysTest extends \PHPUnit_Framework_TestCase
{
	protected $config;

	protected $loadWithData = [
		'a' => ['id' => 1, 'name' => 'a', 'description' => 'aa', 'tags' => ['t.a', 't.aa']],
		'b' => ['id' => 2, 'name' => 'b', 'description' => 'bb', 'tags' => ['t.b', 't.bb']],
		'c' => ['id' => 3, 'name' => 'c', 'description' => 'cc', 'tags' => ['t.c', 't.c']],
		'd' => ['id' => 4, 'name' => 'd', 'description' => 'dd', 'tags' => ['t.d', 't.dd']],
	];

	public function setUp()
	{
		parent::setUp();

		$this->config = new Config('pp', 'b', 'de');
		$this->config->setApplicationName('appName')->setBaseUrl('a');
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->config = null;
	}

	/**
	 * @covers ::addTag
	 */
	public function testAddTagCreateSuccess()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'create', 'update'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->once())->method('create')->with('nuff', '', ['tag'])->willReturn(true);
		$keys->expects($this->never())->method('update');

		$this->assertTrue($keys->addTag('nuff', 'tag'));
	}

	/**
	 * @covers ::addTag
	 */
	public function testAddTagCreateFailed()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'create', 'update'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->once())->method('create')->with('nuff', '', ['tag'])->willReturn(false);
		$keys->expects($this->never())->method('update');

		$this->assertFalse($keys->addTag('nuff', 'tag'));
	}

	/**
	 * @covers ::addTag
	 */
	public function testAddTagUpdateSuccess()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'create', 'update'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->never())->method('create');
		$keys->expects($this->once())->method('update')->with('a', 'a', 'aa', ['t.a', 't.aa', 'tag'])->willReturn(true);

		$this->assertTrue($keys->addTag('a', 'tag'));
	}

	/**
	 * @covers ::addTag
	 */
	public function testAddTagUpdateFailed()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'create', 'update'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->never())->method('create');
		$keys->expects($this->once())->method('update')->with('a', 'a', 'aa', ['t.a', 't.aa', 'tag'])->willReturn(false);

		$this->assertFalse($keys->addTag('a', 'tag'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateSuccess()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'methodPost'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->once())->method('methodPost')->with(Keys::URL_API, [
			'name' => 'name',
			'description' => 'description',
			'tags' => 'a,b,c'
		])->willReturn([
			'name' => 'name',
			'description' => 'description',
			'tags' => ['a', 'b', 'c']
		]);

		$this->assertTrue($keys->create('name', 'description', ['a', 'b', 'c']));
		$this->assertCount(5, $keys->getCollection());
		$this->assertEquals([
			'name' => 'name',
			'description' => 'description',
			'tags' => ['a', 'b', 'c']
		], $keys->getCollection()->get('name'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateFailed()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'methodPost'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->once())->method('methodPost')->with(Keys::URL_API, [
			'name' => 'name',
			'description' => 'description',
			'tags' => 'a,b,c'
		])->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($keys->create('name', 'description', ['a', 'b', 'c']));
		$this->assertCount(4, $keys->getCollection());
		$this->assertFalse($keys->getCollection()->offsetExists('name'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteWithMany()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['deleteMany', 'methodDelete'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('deleteMany')->with(['a', 'b'])->willReturn(true);
		$keys->expects($this->never())->method('methodDelete');

		$this->assertTrue($keys->delete(['a', 'b']));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteOnlyOneSuccess()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'deleteMany', 'methodDelete'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->never())->method('deleteMany');
		$keys->expects($this->once())->method('methodDelete')->with(Keys::URL_API . '2')->willReturn(null);

		$this->assertTrue($keys->delete('b'));
		$this->assertCount(3, $keys->getCollection());
		$this->assertFalse($keys->getCollection()->offsetExists('b'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteOnlyOneFailedByException()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'deleteMany', 'methodDelete'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->never())->method('deleteMany');
		$keys->expects($this->once())->method('methodDelete')->with(Keys::URL_API . '2')->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($keys->delete('b'));
		$this->assertCount(4, $keys->getCollection());
		$this->assertTrue($keys->getCollection()->offsetExists('b'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNotExists()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'deleteMany', 'methodDelete'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->never())->method('deleteMany');
		$keys->expects($this->never())->method('methodDelete');

		$this->assertTrue($keys->delete('vrfeiopwnjgoiera'));
	}

	/**
	 * @covers ::deleteMany
	 */
	public function testDeleteManySuccessByEmptyCount()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['delete'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->never())->method('delete');

		$reflectionMethod = new \ReflectionMethod($keys, 'deleteMany');
		$reflectionMethod->setAccessible(true);

		$this->assertTrue($reflectionMethod->invoke($keys, []));
	}

	/**
	 * @covers ::deleteMany
	 */
	public function testDeleteManyFailedByDeleteResult()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['delete'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('delete')->with('a')->willReturn(false);

		$reflectionMethod = new \ReflectionMethod($keys, 'deleteMany');
		$reflectionMethod->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($keys, ['a', 'b']));
	}

	/**
	 * @covers ::deleteMany
	 */
	public function testDeleteManySuccess()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['delete'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->exactly(2))->method('delete')->withConsecutive(['a'], ['b'])->willReturn(true);

		$reflectionMethod = new \ReflectionMethod($keys, 'deleteMany');
		$reflectionMethod->setAccessible(true);

		$this->assertTrue($reflectionMethod->invoke($keys, ['a', 'b']));
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetch()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'methodGet'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->never())->method('methodGet');

		$this->assertEquals(['a', 'b', 'c', 'd'], $keys->fetch());
	}

	public function dataProviderUpdateSuccess()
	{
		return [
			['a', 'name', null, null],
			['a', 'name', 'desc', null],
			['a', 'name', null, ['a', 'b', 'c']],
			['a', 'name', 'desc', ['a', 'b', 'c']],
		];
	}

	/**
	 * @covers ::update
	 * @dataProvider dataProviderUpdateSuccess
	 */
	public function testUpdateSuccess($key, $name, $description, $tags)
	{
		$trKey = array_merge($this->loadWithData[$key], [
			'name' => $name,
			'description' => $description !== null ? $description : $this->loadWithData[$key]['description'],
			'tags' => $tags !== null ? $tags : $this->loadWithData[$key]['tags']
		]);

		$trKeyRequest = $trKey;
		$trKeyRequest['tags'] = implode(',', $trKeyRequest['tags']);

		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'methodPatch'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->once())->method('methodPatch')->with(Keys::URL_API . $this->loadWithData[$key]['id'], $trKeyRequest)->willReturn($trKey);

		$this->assertTrue($keys->update($key, $name, $description, $tags));

		$this->assertEquals($trKey, $keys->getCollection()->get($name));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateFailed()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['load', 'methodPatch'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('load')->with()->willReturn($this->loadWithData);
		$keys->expects($this->once())->method('methodPatch')->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($keys->update('a', 'aa'));
	}

	/**
	 * @covers ::getIdKey
	 */
	public function testGetIdKey()
	{
		$keys = new Keys($this->config);

		$reflectionMethod = new \ReflectionMethod($keys, 'getIdKey');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('name', $reflectionMethod->invoke($keys));
	}


	/**
	 * @covers ::load
	 */
	public function testLoadSuccess()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('methodGet')->with(Keys::URL_API)->willReturn([
			['name' => 'abc'],
			['name' => 'a/b/c'],
		]);

		$reflectionMethod = new \ReflectionMethod($keys, 'load');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([
			['name' => 'abc'],
			['name' => 'a/b/c'],
		], $reflectionMethod->invoke($keys));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadFailed()
	{
		$keys = $this->getMockBuilder(Keys::class)->setMethods(['methodGet'])->setConstructorArgs([$this->config])->getMock();
		$keys->expects($this->once())->method('methodGet')->with(Keys::URL_API)->willThrowException(new \DasRed\PhraseApp\Exception());

		$reflectionMethod = new \ReflectionMethod($keys, 'load');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([], $reflectionMethod->invoke($keys));
	}
}
