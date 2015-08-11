<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Request;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Response;
use DasRed\PhraseApp\Request\Exception\HttpStatus;
use DasRed\PhraseApp\Request\Exception\Json;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$request = new Request('a', 'b');

		$this->assertSame('a/', $request->getBaseUrl());
		$this->assertSame('b', $request->getAuthToken());
	}

	/**
	 * @covers ::getAuthToken
	 * @covers ::setAuthToken
	 */
	public function testGetSetAuthToken()
	{
		$request = new Request('a', 'b');

		$reflectionMethod = new \ReflectionMethod($request, 'setAuthToken');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('b', $request->getAuthToken());
		$this->assertSame($request, $reflectionMethod->invoke($request, 'c'));
		$this->assertSame('c', $request->getAuthToken());
	}

	/**
	 * @covers ::getBaseUrl
	 * @covers ::setBaseUrl
	 */
	public function testGetSetBaseUrl()
	{
		$request = new Request('a', 'b');

		$reflectionMethod = new \ReflectionMethod($request, 'setBaseUrl');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('a/', $request->getBaseUrl());
		$this->assertSame($request, $reflectionMethod->invoke($request, 'c'));
		$this->assertSame('c/', $request->getBaseUrl());
		$this->assertSame($request, $reflectionMethod->invoke($request, 'd/'));
		$this->assertSame('d/', $request->getBaseUrl());
	}

	/**
	 * @covers ::getClient
	 */
	public function testGetClient()
	{
		$request = new Request('a', 'b');

		$reflectionMethod = new \ReflectionMethod($request, 'getClient');
		$reflectionMethod->setAccessible(true);

		$client = $reflectionMethod->invoke($request);
		$adapter = $client->getAdapter();

		$this->assertInstanceOf(Client::class, $client);
		$this->assertInstanceOf(Curl::class, $adapter);

		$this->assertArrayHasKey('curloptions', $adapter->getConfig());

		$this->assertArrayHasKey(CURLOPT_RETURNTRANSFER, $adapter->getConfig()['curloptions']);
		$this->assertTrue($adapter->getConfig()['curloptions'][CURLOPT_RETURNTRANSFER]);

		$this->assertArrayHasKey(CURLOPT_SSL_VERIFYPEER, $adapter->getConfig()['curloptions']);
		$this->assertFalse($adapter->getConfig()['curloptions'][CURLOPT_SSL_VERIFYPEER]);
	}

	/**
	 * @covers ::methodDelete
	 */
	public function testMethodDelete()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_DELETE, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodDelete');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::methodGet
	 */
	public function testMethodGet()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_GET, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodGet');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::methodPatch
	 */
	public function testMethodPatch()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_PATCH, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodPatch');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::methodPost
	 */
	public function testMethodPost()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_POST, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodPost');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::methodPut
	 */
	public function testMethodPut()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_PUT, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodPut');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::request
	 */
	public function testRequestFailedByException()
	{
		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->never())->method('getHeaders');

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->never())->method('getStatusCode')->willReturn(200);
		$response->expects($this->never())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->never())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
			'auth_token' => 'b',
			'project_auth_token' => 'b',
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willThrowException(new \Exception('nuff', 10));

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(\DasRed\PhraseApp\Request\Exception::class, 'nuff', 10);
		$reflectionMethod->invoke($request, 'b', Request::METHOD_GET, ['a' => 1]);
	}

	/**
	 * @covers ::request
	 */
	public function testRequestFailedByStatusCode()
	{
		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->never())->method('getHeaders');

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->exactly(2))->method('getStatusCode')->willReturn(401);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->never())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
			'auth_token' => 'b',
			'project_auth_token' => 'b',
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(HttpStatus::class, '{"success": true}', 401);
		$reflectionMethod->invoke($request, 'b', Request::METHOD_GET, ['a' => 1]);
	}

	/**
	 * @covers ::request
	 */
	public function testRequestFailedByJson()
	{
		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->never())->method('getHeaders');

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->never())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
			'auth_token' => 'b',
			'project_auth_token' => 'b',
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(Json::class, 'Syntax error', 4);
		$reflectionMethod->invoke($request, 'b', Request::METHOD_GET, ['a' => 1]);
	}

	/**
	 * @covers ::request
	 */
	public function testRequestSuccessMethodGET()
	{
		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->never())->method('getHeaders');

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->never())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
			'auth_token' => 'b',
			'project_auth_token' => 'b',
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($request, 'b', Request::METHOD_GET, ['a' => 1]));
	}

	/**
	 * @covers ::request
	 */
	public function testRequestSuccessMethodPOST()
	{
		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->never())->method('getHeaders');

		$response = $this->getMockBuilder(Response::class)->setMethods(['getRequest', 'getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->never())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_POST)->willReturnSelf();
		$client->expects($this->once())->method('setParameterPost')->with([
			'a' => 1,
			'auth_token' => 'b',
			'project_auth_token' => 'b',
		])->willReturnSelf();
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($request, 'b', Request::METHOD_POST, ['a' => 1]));
	}

	/**
	 * @covers ::request
	 */
	public function testRequestSuccessMethodDELETE()
	{
		$jsonParameters = json_encode([
			'auth_token' => 'b',
			'project_auth_token' => 'b',
			'a' => 1,
		]);

		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(2))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['Content-Length', strlen($jsonParameters)]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->once())->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->once())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_DELETE)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->once())->method('setRawBody')->with($jsonParameters)->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($request, 'b', Request::METHOD_DELETE, ['a' => 1]));
	}

	/**
	 * @covers ::request
	 */
	public function testRequestSuccessMethodPUT()
	{
		$jsonParameters = json_encode([
			'auth_token' => 'b',
			'project_auth_token' => 'b',
			'a' => 1,
		]);

		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(2))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['Content-Length', strlen($jsonParameters)]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->once())->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->once())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_PUT)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->once())->method('setRawBody')->with($jsonParameters)->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($request, 'b', Request::METHOD_PUT, ['a' => 1]));
	}

	/**
	 * @covers ::request
	 */
	public function testRequestSuccessMethodPATCH()
	{
		$jsonParameters = json_encode([
			'auth_token' => 'b',
			'project_auth_token' => 'b',
			'a' => 1,
		]);

		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(2))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['Content-Length', strlen($jsonParameters)]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->once())->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->once())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_PATCH)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->once())->method('setRawBody')->with($jsonParameters)->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($request, 'b', Request::METHOD_PATCH, ['a' => 1]));
	}

	/**
	 * @covers ::request
	 */
	public function testRequestSuccessMethodHEAD()
	{
		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->never())->method('getHeaders');

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->never())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(\Zend\Http\Request::METHOD_HEAD)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
			'auth_token' => 'b',
			'project_auth_token' => 'b',
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b'])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($request, 'b', \Zend\Http\Request::METHOD_HEAD, ['a' => 1]));
	}
}
