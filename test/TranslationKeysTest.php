<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\TranslationKeys;
/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\TranslationKeys
 */
class TranslationKeysTest extends \PHPUnit_Framework_TestCase
{
	public function dataProviderAddTag()
	{
		return [
			['abc.def', 'nuffnuff', ['id' => 1, 'tag_names' => 'narf'], 1, ['id' => 1, 'tag_names' => 'nuffnuff']],
			['abc.def', 'nuffnuff', ['id' => 1, 'tag_list' => 'narf', 'tag_names' => 'narf'], 1, ['id' => 1, 'tag_names' => 'nuffnuff']],
			['abc.def', 'nuffnuff', ['id' => 1, 'tag_list' => ['narf'], 'tag_names' => 'narf'], 1, ['id' => 1, 'tag_names' => 'narf,nuffnuff']],
		];
	}

	/**
	 * @covers ::addTag
	 * @dataProvider dataProviderAddTag
	 */
	public function testAddTagSuccess($key, $tag, $get, $id, $trKey)
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['get', 'methodPatch', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('get')->with($key)->willReturn($get);
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');
		$translations->expects($this->once())->method('methodPatch')->with('translation_keys/' . $id, [
			'auth_token' => 'getSessionToken',
			'translation_key' => $trKey
		])->willReturn([]);

		$this->assertTrue($translations->addTag($key, $tag));
	}

	/**
	 * @covers ::addTag
	 */
	public function testAddTagFailed()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['get', 'methodPatch', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('get')->willReturn(['id' => 1]);
		$translations->expects($this->once())->method('getSessionToken')->willReturn('getSessionToken');
		$translations->expects($this->once())->method('methodPatch')->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($translations->addTag('abc.def', 'nuffnuff'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateSuccess()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodPost', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');
		$translations->expects($this->once())->method('methodPost')->with('translation_keys/', [
			'auth_token' => 'getSessionToken',
			'translation_key' => [
				'name' => 'name',
				'description' => 'description',
				'data_type' => 'stringer',
				'tag_names' => 'a,b,c'
			]
		])->willReturn([]);

		$this->assertTrue($translations->create('name', 'description', ['a', 'b', 'c'], 'stringer'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateFailed()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodPost', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');
		$translations->expects($this->once())->method('methodPost')->with('translation_keys/', [
			'auth_token' => 'getSessionToken',
			'translation_key' => [
				'name' => 'name',
				'description' => 'description',
				'data_type' => 'stringer',
				'tag_names' => 'a,b,c'
			]
		])->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($translations->create('name', 'description', ['a', 'b', 'c'], 'stringer'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteWithMany()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['deleteMany', 'getId', 'methodDelete', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('deleteMany')->with(['a', 'b'])->willReturn(true);
		$translations->expects($this->never())->method('getId');
		$translations->expects($this->never())->method('methodDelete');
		$translations->expects($this->never())->method('getSessionToken');

		$this->assertTrue($translations->delete(['a', 'b']));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteOnlyOneSuccess()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['deleteMany', 'getId', 'methodDelete', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->never())->method('deleteMany');
		$translations->expects($this->once())->method('getId')->with('abc.def')->willReturn(1);
		$translations->expects($this->once())->method('methodDelete')->with('translation_keys/1', [
			'auth_token' => 'getSessionToken'
		])->willReturn(['success' => true]);
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');

		$this->assertTrue($translations->delete('abc.def'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteOnlyOneFailedByResultSuccess()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['deleteMany', 'getId', 'methodDelete', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->never())->method('deleteMany');
		$translations->expects($this->once())->method('getId')->with('abc.def')->willReturn(1);
		$translations->expects($this->once())->method('methodDelete')->with('translation_keys/1', [
			'auth_token' => 'getSessionToken'
		])->willReturn(['success' => false]);
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');

		$this->assertFalse($translations->delete('abc.def'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteOnlyOneFailedByException()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['deleteMany', 'getId', 'methodDelete', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->never())->method('deleteMany');
		$translations->expects($this->once())->method('getId')->with('abc.def')->willReturn(1);
		$translations->expects($this->once())->method('methodDelete')->with('translation_keys/1', [
			'auth_token' => 'getSessionToken'
		])->willThrowException(new \DasRed\PhraseApp\Exception());
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');

		$this->assertFalse($translations->delete('abc.def'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNotExists()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['deleteMany', 'getId', 'methodDelete', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->never())->method('deleteMany');
		$translations->expects($this->once())->method('getId')->with('abc.def')->willReturn(false);
		$translations->expects($this->never())->method('methodDelete');
		$translations->expects($this->never())->method('getSessionToken');

		$this->assertTrue($translations->delete('abc.def'));
	}

	/**
	 * @covers ::deleteMany
	 */
	public function testDeleteManySuccessByEmptyCount()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetchIds', 'methodDelete', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('fetchIds')->with(['a', 'b'])->willReturn([]);
		$translations->expects($this->never())->method('methodDelete');
		$translations->expects($this->never())->method('getSessionToken');

		$reflectionMethod = new \ReflectionMethod($translations, 'deleteMany');
		$reflectionMethod->setAccessible(true);

		$this->assertTrue($reflectionMethod->invoke($translations, ['a', 'b']));
	}

	/**
	 * @covers ::deleteMany
	 */
	public function testDeleteManyFailedByException()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetchIds', 'methodDelete', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('fetchIds')->with(['a', 'b'])->willReturn([1]);
		$translations->expects($this->once())->method('methodDelete')->with('translation_keys/destroy_multiple', [
			'ids' => [1],
			'auth_token' => 'getSessionToken'
		])->willThrowException(new \DasRed\PhraseApp\Exception());
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');

		$reflectionMethod = new \ReflectionMethod($translations, 'deleteMany');
		$reflectionMethod->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($translations, ['a', 'b']));
	}

	/**
	 * @covers ::deleteMany
	 */
	public function testDeleteManyFailedByResultSuccess()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetchIds', 'methodDelete', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('fetchIds')->with(['a', 'b'])->willReturn([1]);
		$translations->expects($this->once())->method('methodDelete')->with('translation_keys/destroy_multiple', [
			'ids' => [1],
			'auth_token' => 'getSessionToken'
		])->willReturn(['success' => false]);
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');

		$reflectionMethod = new \ReflectionMethod($translations, 'deleteMany');
		$reflectionMethod->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($translations, ['a', 'b']));
	}

	/**
	 * @covers ::deleteMany
	 */
	public function testDeleteManySuccess()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['fetchIds', 'methodDelete', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('fetchIds')->with(['a', 'b'])->willReturn([1]);
		$translations->expects($this->once())->method('methodDelete')->with('translation_keys/destroy_multiple', [
			'ids' => [1],
			'auth_token' => 'getSessionToken'
		])->willReturn(['success' => true]);
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');

		$reflectionMethod = new \ReflectionMethod($translations, 'deleteMany');
		$reflectionMethod->setAccessible(true);

		$this->assertTrue($reflectionMethod->invoke($translations, ['a', 'b']));
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetchSuccess()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translation_keys/')->willReturn([
			['name' => 'abc'],
			['name' => 'a/b/c'],
		]);

		$this->assertEquals(['abc', 'a/b/c'], $translations->fetch());
	}

	/**
	 * @covers ::fetch
	 */
	public function testFetchFailed()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translation_keys/')->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertEquals([], $translations->fetch());
	}

	/**
	 * @covers ::fetchIds
	 */
	public function testFetchIdsSuccess()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translation_keys/', [
			'key_names' => ['a', 'a/b/c']
		])->willReturn([
			['id' => 1],
			['id' => 2],
		]);

		$reflectionMethod = new \ReflectionMethod($translations, 'fetchIds');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([1, 2], $reflectionMethod->invoke($translations, ['a', 'a/b/c']));
	}

	/**
	 * @covers ::fetchIds
	 */
	public function testFetchIdsFailed()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translation_keys/', [
			'key_names' => ['a', 'a/b/c']
		])->willThrowException(new \DasRed\PhraseApp\Exception());

		$reflectionMethod = new \ReflectionMethod($translations, 'fetchIds');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals([], $reflectionMethod->invoke($translations, ['a', 'a/b/c']));
	}

	/**
	 * @covers ::get
	 */
	public function testGetSuccess()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translation_keys/', [
			'key_names' => ['a/b/c']
		])->willReturn([
			['id' => 1],
		]);

		$reflectionMethod = new \ReflectionMethod($translations, 'get');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['id' => 1], $reflectionMethod->invoke($translations, 'a/b/c'));
	}

	/**
	 * @covers ::get
	 */
	public function testGetFailedByException()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translation_keys/', [
			'key_names' => ['a/b/c']
		])->willThrowException(new \DasRed\PhraseApp\Exception());

		$reflectionMethod = new \ReflectionMethod($translations, 'get');
		$reflectionMethod->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($translations, 'a/b/c'));
	}

	/**
	 * @covers ::get
	 */
	public function testGetFailedByResult()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translation_keys/', [
			'key_names' => ['a/b/c']
		])->willReturn([]);

		$reflectionMethod = new \ReflectionMethod($translations, 'get');
		$reflectionMethod->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($translations, 'a/b/c'));
	}

	/**
	 * @covers ::getId
	 */
	public function testGetId()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['methodGet'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('methodGet')->with('translation_keys/', [
			'key_names' => ['a/b/c']
		])->willReturn([
			['id' => 1],
		]);

		$reflectionMethod = new \ReflectionMethod($translations, 'getId');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(1, $reflectionMethod->invoke($translations, 'a/b/c'));
	}

	public function dataProviderUpdateSuccess()
	{
		return [
			['abc.def', null, null, null, null, 1, ['id' => 1], ['id' => 1]],
			['abc.def', 'name', null, null, null, 1, ['id' => 1], ['id' => 1, 'name' => 'name']],
			['abc.def', null, 'desc', null, null, 1, ['id' => 1], ['id' => 1, 'description' => 'desc']],
			['abc.def', null, null, ['a', 'b', 'c'], null, 1, ['id' => 1], ['id' => 1, 'tag_names' => 'a,b,c']],
			['abc.def', null, null, null, 'dataType', 1, ['id' => 1], ['id' => 1, 'data_type' => 'dataType']],
			['abc.def', 'name', 'desc', ['a', 'b', 'c'], 'dataType', 1, ['id' => 1], ['id' => 1, 'name' => 'name', 'description' => 'desc', 'tag_names' => 'a,b,c', 'data_type' => 'dataType']],

			['abc.def', null, null, null, null, 1, ['id' => 1, 'name' => 'name', 'description' => 'desc', 'tag_list' => ['a', 'b', 'c'], 'data_type' => 'dataType'], ['id' => 1, 'name' => 'name', 'description' => 'desc', 'tag_names' => 'a,b,c', 'data_type' => 'dataType']],
			['abc.def', 'name1', 'desc1', ['a1', 'b1', 'c1'], 'dataType1', 1, ['id' => 1, 'name' => 'name', 'description' => 'desc', 'tag_list' => ['a', 'b', 'c'], 'data_type' => 'dataType'], ['id' => 1, 'name' => 'name1', 'description' => 'desc1', 'tag_names' => 'a1,b1,c1', 'data_type' => 'dataType1']],
		];
	}

	/**
	 * @covers ::update
	 * @dataProvider dataProviderUpdateSuccess
	 */
	public function testUpdateSuccess($key, $name, $description, $tags, $dataType, $id, $get, $trKey)
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['get', 'methodPatch', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('get')->with($key)->willReturn($get);
		$translations->expects($this->once())->method('getSessionToken')->with()->willReturn('getSessionToken');
		$translations->expects($this->once())->method('methodPatch')->with('translation_keys/' . $id, [
			'auth_token' => 'getSessionToken',
			'translation_key' => $trKey
		])->willReturn([]);

		$this->assertTrue($translations->update($key, $name, $description, $tags, $dataType));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateFailed()
	{
		$translations = $this->getMockBuilder(TranslationKeys::class)->setMethods(['get', 'methodPatch', 'getSessionToken'])->disableOriginalConstructor()->getMock();
		$translations->expects($this->once())->method('get')->willReturn(['id' => 1]);
		$translations->expects($this->once())->method('getSessionToken')->willReturn('getSessionToken');
		$translations->expects($this->once())->method('methodPatch')->willThrowException(new \DasRed\PhraseApp\Exception());

		$this->assertFalse($translations->update('a'));
	}
}
