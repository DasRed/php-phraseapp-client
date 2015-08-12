<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Request;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Response;
use DasRed\PhraseApp\Request\Exception\HttpStatus;
use DasRed\PhraseApp\Request\Exception\Json;
use DasRed\PhraseApp\Config;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
	protected $config;

	public function setUp()
	{
		$this->config = new Config('pp', 'b', 'de', 'userAgentName', 'a');
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->config = null;
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$config = new Config('pp', 'a', 'de');
		$request = new Request($config);

		$this->assertSame($config, $request->getConfig());
	}

	/**
	 * @covers ::getClient
	 */
	public function testGetClient()
	{
		$request = new Request($this->config);

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
	public function testMethodDeleteWithParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_DELETE, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodDelete');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::methodDelete
	 */
	public function testMethodDeleteWithoutParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_DELETE, null)->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodDelete');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c'));
	}

	/**
	 * @covers ::methodGet
	 */
	public function testMethodGetWithParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_GET, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodGet');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::methodGet
	 */
	public function testMethodGetWithoutParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_GET)->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodGet');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c'));
	}

	/**
	 * @covers ::methodPatch
	 */
	public function testMethodPatchWithParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_PATCH, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodPatch');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::methodPatch
	 */
	public function testMethodPatchWithoutParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_PATCH, null)->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodPatch');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c'));
	}

	/**
	 * @covers ::methodPost
	 */
	public function testMethodPostWithParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_POST, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodPost');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::methodPost
	 */
	public function testMethodPostWithoutParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_POST, null)->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodPost');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c'));
	}

	/**
	 * @covers ::methodPut
	 */
	public function testMethodPutWithParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_PUT, ['b' => 10])->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodPut');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c', ['b' => 10]));
	}

	/**
	 * @covers ::methodPut
	 */
	public function testMethodPutWithoutParameters()
	{
		$request = $this->getMockBuilder(Request::class)->setMethods(['request'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('request')->with('b/c', Request::METHOD_PUT, null)->willReturn(['a' => 1]);

		$reflectionMethod = new \ReflectionMethod($request, 'methodPut');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['a' => 1], $reflectionMethod->invoke($request, 'b/c'));
	}

	/**
	 * @covers ::request
	 */
	public function testRequestFailedByException()
	{
		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(3))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->once())->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->never())->method('getStatusCode')->willReturn(200);
		$response->expects($this->never())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->once())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willThrowException(new \Exception('nuff', 10));

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
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
		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(3))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->once())->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->exactly(2))->method('getStatusCode')->willReturn(401);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->once())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
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
		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(3))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->once())->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->once())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
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
		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(3))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()]
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
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
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
		$jsonParameters = json_encode([
			'a' => 1,
		]);

		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(4))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()],
			['Content-Length', strlen($jsonParameters)]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->exactly(2))->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->exactly(2))->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_POST)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->once())->method('setRawBody')->with($jsonParameters)->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
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
			'a' => 1,
		]);

		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(4))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()],
			['Content-Length', strlen($jsonParameters)]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->exactly(2))->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->exactly(2))->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_DELETE)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->once())->method('setRawBody')->with($jsonParameters)->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
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
			'a' => 1,
		]);

		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(4))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()],
			['Content-Length', strlen($jsonParameters)]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->exactly(2))->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->exactly(2))->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_PUT)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->once())->method('setRawBody')->with($jsonParameters)->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
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
			'a' => 1,
		]);

		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(4))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()],
			['Content-Length', strlen($jsonParameters)]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->exactly(2))->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->exactly(2))->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_PATCH)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->once())->method('setRawBody')->with($jsonParameters)->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
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
		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(3))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()]
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
		$client->expects($this->once())->method('setMethod')->with(\Zend\Http\Request::METHOD_HEAD)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->once())->method('setParameterGet')->with([
			'a' => 1,
		])->willReturnSelf();
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($request, 'b', \Zend\Http\Request::METHOD_HEAD, ['a' => 1]));
	}

	/**
	 * @covers ::request
	 */
	public function testRequestSuccessWithoutParameters()
	{
		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(3))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()]
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
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($request, 'b', Request::METHOD_GET));
	}

	/**
	 * @covers ::request
	 */
	public function testRequestSuccessWithProjectId()
	{
		$headers = $this->getMockBuilder(\Zend\Http\Headers::class)->setMethods(['addHeaderLine'])->disableOriginalConstructor()->getMock();
		$headers->expects($this->exactly(3))->method('addHeaderLine')->withConsecutive(
			['Content-Type', 'application/json'],
			['User-Agent', $this->config->getApplicationName()],
			['Authorization', 'token ' . $this->config->getAccessToken()]
		)->willReturnSelf();

		$request = $this->getMockBuilder(\Zend\Http\Request::class)->setMethods(['getHeaders'])->disableOriginalConstructor()->getMock();
		$request->expects($this->once())->method('getHeaders')->with()->willReturn($headers);

		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);
		$response->expects($this->once())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['getRequest', 'reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->once())->method('getRequest')->with()->willReturn($request);
		$client->expects($this->once())->method('reset')->with()->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('https://api.phraseapp.com/api/v2/projects/abcd1234cdef1234abcd1234cdef1234/translations/snuff/abcd1234cdef1234abcd1234cdef1234/nuff')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_GET)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$request = $this->getMockBuilder(Request::class)->setMethods(['getClient'])->setConstructorArgs([$this->config])->getMock();
		$request->expects($this->any())->method('getClient')->willReturn($client);

		$this->config->setProjectId('abcd1234cdef1234abcd1234cdef1234')->setBaseUrl('https://api.phraseapp.com/api/v2/projects/:project_id/translations');

		$reflectionMethod = new \ReflectionMethod($request, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($request, 'snuff/:PROJECT_ID/nuff', Request::METHOD_GET));
	}
}
