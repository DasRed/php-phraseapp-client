<?php
namespace DasRedTest\PhraseApp;

use DasRed\PhraseApp\Sessions;
use DasRed\PhraseApp\Sessions\Exception\LoginFailed;
use Zend\Http\Client;
use Zend\Http\Response;
use DasRed\PhraseApp\Request;
/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Sessions
 */
class SessionsTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$session = $this->getMockBuilder(Sessions::class)->setMethods(['logout'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();

		$this->assertSame('a/', $session->getBaseUrl());
		$this->assertSame('b', $session->getAuthToken());
		$this->assertSame('c', $session->getUserEmail());
		$this->assertSame('d', $session->getUserPassword());
	}

	/**
	 * @covers ::__destruct
	 */
	public function testDestruct()
	{
		$session = $this->getMockBuilder(Sessions::class)->setMethods(['logout'])->disableOriginalConstructor()->getMock();
		$session->expects($this->once())->method('logout')->with()->willReturn(true);

		$session->__destruct();
	}

	/**
	 * @covers ::getSessionToken
	 */
	public function testGetSessionTokenSuccess()
	{
		$session = $this->getMockBuilder(Sessions::class)->setMethods(['login', 'logout'])->disableOriginalConstructor()->getMock();
		$session->expects($this->once())->method('login')->with()->willReturnCallback(function() use ($session)
		{
			$reflectionProperty = new \ReflectionProperty($session, 'sessionToken');
			$reflectionProperty->setAccessible(true);
			$reflectionProperty->setValue($session, 'a');

			return true;
		});

		$reflectionMethod = new \ReflectionMethod($session, 'getSessionToken');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('a', $reflectionMethod->invoke($session));
		$this->assertSame('a', $reflectionMethod->invoke($session));
	}

	/**
	 * @covers ::getSessionToken
	 */
	public function testGetSessionTokenFailed()
	{
		$session = $this->getMockBuilder(Sessions::class)->setMethods(['login', 'logout'])->disableOriginalConstructor()->getMock();
		$session->expects($this->once())->method('login')->with()->willReturn(false);

		$reflectionMethod = new \ReflectionMethod($session, 'getSessionToken');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(LoginFailed::class);
		$this->assertSame('a', $reflectionMethod->invoke($session));
	}

	/**
	 * @covers ::getUserEmail
	 * @covers ::setUserEmail
	 */
	public function testGetSetUserEmail()
	{
		$session = $this->getMockBuilder(Sessions::class)->setMethods(['logout'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();

		$reflectionMethod = new \ReflectionMethod($session, 'setUserEmail');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('c', $session->getUserEmail());
		$this->assertSame($session, $reflectionMethod->invoke($session, 'nuff'));
		$this->assertSame('nuff', $session->getUserEmail());

	}

	/**
	 * @covers ::getUserPassword
	 * @covers ::setUserPassword
	 */
	public function testGetSetUserPassword()
	{
		$session = $this->getMockBuilder(Sessions::class)->setMethods(['logout'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();

		$reflectionMethod = new \ReflectionMethod($session, 'setUserPassword');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('d', $session->getUserPassword());
		$this->assertSame($session, $reflectionMethod->invoke($session, 'nuff'));
		$this->assertSame('nuff', $session->getUserPassword());
	}

	/**
	 * @covers ::login
	 */
	public function testLoginSuccess()
	{
		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->any())->method('getStatusCode')->willReturn(200);
		$response->expects($this->any())->method('getBody')->willReturn('{"success": true, "auth_token": "nuff"}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->any())->method('reset')->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/sessions/')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_POST)->willReturnSelf();
		$client->expects($this->once())->method('setParameterPost')->with([
			'auth_token' => 'b',
			'project_auth_token' => 'b',
			'email' => 'c',
			'password' => 'd'
		])->willReturnSelf();
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$session = $this->getMockBuilder(Sessions::class)->setMethods(['getClient', 'logout'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();
		$session->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($session, 'login');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($session, 'sessionToken');
		$reflectionProperty->setAccessible(true);

		$this->assertTrue($reflectionMethod->invoke($session));
		$this->assertSame('nuff', $reflectionProperty->getValue($session));

		$this->assertTrue($reflectionMethod->invoke($session));
		$this->assertSame('nuff', $reflectionProperty->getValue($session));
	}

	/**
	 * @covers ::login
	 */
	public function testLoginFailedByResponse()
	{
		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->any())->method('getStatusCode')->willReturn(200);
		$response->expects($this->any())->method('getBody')->willReturn('{"success": false}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->any())->method('reset')->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/sessions/')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_POST)->willReturnSelf();
		$client->expects($this->once())->method('setParameterPost')->with([
			'auth_token' => 'b',
			'project_auth_token' => 'b',
			'email' => 'c',
			'password' => 'd'
		])->willReturnSelf();
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$session = $this->getMockBuilder(Sessions::class)->setMethods(['getClient', 'logout'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();
		$session->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($session, 'login');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($session, 'sessionToken');
		$reflectionProperty->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($session));
		$this->assertNull($reflectionProperty->getValue($session));
	}

	/**
	 * @covers ::login
	 */
	public function testLoginFailedByException()
	{
		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->any())->method('getStatusCode')->willReturn(200);
		$response->expects($this->any())->method('getBody')->willReturn('{"success": false}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->any())->method('reset')->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/sessions/')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_POST)->willReturnSelf();
		$client->expects($this->once())->method('setParameterPost')->with([
			'auth_token' => 'b',
			'project_auth_token' => 'b',
			'email' => 'c',
			'password' => 'd'
		])->willReturnSelf();
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willThrowException(new \Exception());

		$session = $this->getMockBuilder(Sessions::class)->setMethods(['getClient', 'logout'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();
		$session->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($session, 'login');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($session, 'sessionToken');
		$reflectionProperty->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($session));
		$this->assertNull($reflectionProperty->getValue($session));
	}

	/**
	 * @covers ::logout
	 */
	public function testLogoutSuccess()
	{
		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->any())->method('getStatusCode')->willReturn(200);
		$response->expects($this->any())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->any())->method('reset')->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/sessions/')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_DELETE)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet')->with()->willReturnSelf();
		$client->expects($this->once())->method('setRawBody')->with(json_encode([
			'auth_token' => 'nuff',
			'project_auth_token' => 'b'
		]))->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$session = $this->getMockBuilder(Sessions::class)->setMethods(['getClient'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();
		$session->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($session, 'logout');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($session, 'sessionToken');
		$reflectionProperty->setAccessible(true);
		$reflectionProperty->setValue($session, 'nuff');

		$this->assertTrue($reflectionMethod->invoke($session));
		$this->assertNull($reflectionProperty->getValue($session));

		$this->assertTrue($reflectionMethod->invoke($session));
		$this->assertNull($reflectionProperty->getValue($session));
	}

	/**
	 * @covers ::logout
	 */
	public function testLogoutFailedByResponse()
	{
		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->any())->method('getStatusCode')->willReturn(200);
		$response->expects($this->any())->method('getBody')->willReturn('{"success": false}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->any())->method('reset')->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/sessions/')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_DELETE)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet')->with()->willReturnSelf();
		$client->expects($this->once())->method('setRawBody')->with(json_encode([
			'auth_token' => 'nuff',
			'project_auth_token' => 'b'
		]))->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$session = $this->getMockBuilder(Sessions::class)->setMethods(['getClient', '__destruct'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();
		$session->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($session, 'logout');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($session, 'sessionToken');
		$reflectionProperty->setAccessible(true);
		$reflectionProperty->setValue($session, 'nuff');

		$this->assertFalse($reflectionMethod->invoke($session));
		$this->assertSame('nuff', $reflectionProperty->getValue($session));
	}

	/**
	 * @covers ::logout
	 */
	public function testLogoutFailedByException()
	{
		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->any())->method('getStatusCode')->willReturn(200);
		$response->expects($this->any())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['reset', 'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->any())->method('reset')->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/sessions/')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_DELETE)->willReturnSelf();
		$client->expects($this->never())->method('setParameterPost');
		$client->expects($this->never())->method('setParameterGet')->with()->willReturnSelf();
		$client->expects($this->once())->method('setRawBody')->with(json_encode([
			'auth_token' => 'nuff',
			'project_auth_token' => 'b'
		]))->willReturnSelf();
		$client->expects($this->once())->method('send')->with()->willThrowException(new \Exception());

		$session = $this->getMockBuilder(Sessions::class)->setMethods(['getClient', '__destruct'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();
		$session->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($session, 'logout');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($session, 'sessionToken');
		$reflectionProperty->setAccessible(true);
		$reflectionProperty->setValue($session, 'nuff');

		$this->assertFalse($reflectionMethod->invoke($session));
		$this->assertSame('nuff', $reflectionProperty->getValue($session));
	}

	/**
	 * @covers ::request
	 */
	public function testRequest()
	{
		$response = $this->getMockBuilder(Response::class)->setMethods(['getStatusCode', 'getBody'])->disableOriginalConstructor()->getMock();
		$response->expects($this->any())->method('getStatusCode')->willReturn(200);
		$response->expects($this->any())->method('getBody')->willReturn('{"success": true}');

		$client = $this->getMockBuilder(Client::class)->setMethods(['reset',  'setUri', 'setMethod', 'setParameterPost', 'setParameterGet', 'setRawBody', 'send'])->disableOriginalConstructor()->getMock();
		$client->expects($this->any())->method('reset')->willReturnSelf();
		$client->expects($this->once())->method('setUri')->with('a/b')->willReturnSelf();
		$client->expects($this->once())->method('setMethod')->with(Request::METHOD_POST)->willReturnSelf();
		$client->expects($this->once())->method('setParameterPost')->with([
			'auth_token' => 'b',
			'project_auth_token' => 'b',
			'a' => 'nuff'
		])->willReturnSelf();
		$client->expects($this->never())->method('setParameterGet');
		$client->expects($this->never())->method('setRawBody');
		$client->expects($this->once())->method('send')->with()->willReturn($response);

		$session = $this->getMockBuilder(Sessions::class)->setMethods(['getClient', 'logout'])->setConstructorArgs(['a', 'b', 'c', 'd'])->getMock();
		$session->expects($this->any())->method('getClient')->willReturn($client);

		$reflectionMethod = new \ReflectionMethod($session, 'request');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals(['success' => true], $reflectionMethod->invoke($session, 'b', Request::METHOD_POST, ['a' => 'nuff']));
	}
}
