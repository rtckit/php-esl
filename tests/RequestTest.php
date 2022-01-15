<?php

declare(strict_types = 1);

namespace RTCKit\ESL\Tests;

use PHPUnit\Framework\TestCase;

use RTCKit\ESL\{
    Message,
    MessageInterface,
    Request,
    RequestInterface
};
use RTCKit\ESL\Exception\ESLException;

/**
 * Class RequestTest.
 *
 * @covers \RTCKit\ESL\Request
 */
class RequestTest extends TestCase
{
    private Request $request;

    public function setUp(): void
    {
        $this->request = new Request;
    }

    public function testConstructor(): void
    {
        $new = new Request;

        $ref = new \ReflectionProperty($new, 'parameters');
        $ref->setAccessible(true);
        $this->assertFalse($ref->isInitialized($new));

        $other = new Request('param1 param2');
        $ref = new \ReflectionProperty($other, 'parameters');
        $ref->setAccessible(true);
        $parameters = $ref->getValue($other);

        $this->assertEquals('param1 param2', $parameters);
    }

    public function testSetParameters(): void
    {
        $parameters = 'paramA paramB';

        $ret = $this->request->setParameters($parameters);

        $this->assertInstanceOf(RequestInterface::class, $ret);
        $this->assertInstanceOf(Request::class, $ret);

        $ref = new \ReflectionProperty($this->request, 'parameters');
        $ref->setAccessible(true);
        $value = $ref->getValue($this->request);

        $this->assertIsString($value);
        $this->assertEquals($parameters, $value);
    }

    public function testGetParameters(): void
    {
        $parameters = 'paramA paramB';

        $this->request->setParameters($parameters);
        $ref = new \ReflectionProperty($this->request, 'parameters');
        $ref->setAccessible(true);
        $ref->setValue($this->request, $parameters);

        $value = $this->request->getParameters();

        $this->assertIsString($value);
        $this->assertEquals($parameters, $value);

        $new = new Request;
        $ref = new \ReflectionProperty($new, 'parameters');
        $ref->setAccessible(true);

        $this->assertFalse($ref->isInitialized($new));
        $this->assertNull($new->getParameters());
    }

    public function testParseNoParameters(): void
    {
        $request = Request::parse('api');

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(MessageInterface::class, $request);
        $this->assertInstanceOf(Message::class, $request);
        $this->assertInstanceOf(Request\Api::class, $request);
        $this->assertNull($request->getParameters());
    }

    public function testParseWithParametersAndHeaders(): void
    {
        $request = Request::parse("bgapi status\nJob-UUID: d8c7f660-37a6-4e73-9170-1a731c442148");

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(MessageInterface::class, $request);
        $this->assertInstanceOf(Message::class, $request);
        $this->assertInstanceOf(Request\BgApi::class, $request);
        $this->assertNotNull($request->getParameters());
        $this->assertEquals('status', $request->getParameters());
        $this->assertEquals(['job-uuid' => 'd8c7f660-37a6-4e73-9170-1a731c442148'], $request->getHeaders());
    }

    public function testParseWithHeadersAndBody(): void
    {
        $request = Request::parse(
            "sendevent NOTIFY\n" .
            "profile: internal\n" .
            "content-type: application/simple-message-summary\n" .
            "event-string: check-sync\n" .
            "user: 1005\n" .
            "host: 192.168.10.4\n" .
            "Content-Length: 2\n" .
            "\n" .
            "OK"
        );

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(MessageInterface::class, $request);
        $this->assertInstanceOf(Message::class, $request);
        $this->assertInstanceOf(Request\SendEvent::class, $request);
        $this->assertNotNull($request->getParameters());
        $this->assertEquals('NOTIFY', $request->getParameters());
        $this->assertEquals([
            'profile' => 'internal',
            'content-type' => 'application/simple-message-summary',
            'event-string' => 'check-sync',
            'user' => '1005',
            'host' => '192.168.10.4',
            'content-length' => '2',
        ], $request->getHeaders());
        $this->assertEquals('OK', $request->getBody());
    }

    public function testParseByRequestType(): void
    {
        $this->assertInstanceOf(Request\Api::class, Request::parse('api'));
        $this->assertInstanceOf(Request\Auth::class, Request::parse('auth'));
        $this->assertInstanceOf(Request\BgApi::class, Request::parse('bgapi'));
        $this->assertInstanceOf(Request\Connect::class, Request::parse('connect'));
        $this->assertInstanceOf(Request\DivertEvents::class, Request::parse('divert_events'));
        $this->assertInstanceOf(Request\Eksit::class, Request::parse('exit'));
        $this->assertInstanceOf(Request\Event::class, Request::parse('event'));
        $this->assertInstanceOf(Request\Filter::class, Request::parse('filter'));
        $this->assertInstanceOf(Request\Linger::class, Request::parse('linger'));
        $this->assertInstanceOf(Request\Log::class, Request::parse('log'));
        $this->assertInstanceOf(Request\MyEvents::class, Request::parse('myevents'));
        $this->assertInstanceOf(Request\NixEvent::class, Request::parse('nixevent'));
        $this->assertInstanceOf(Request\NoEvents::class, Request::parse('noevents'));
        $this->assertInstanceOf(Request\NoLinger::class, Request::parse('nolinger'));
        $this->assertInstanceOf(Request\NoLog::class, Request::parse('nolog'));
        $this->assertInstanceOf(Request\Resume::class, Request::parse('resume'));
        $this->assertInstanceOf(Request\SendEvent::class, Request::parse('sendevent'));
        $this->assertInstanceOf(Request\SendMsg::class, Request::parse('sendmsg'));

        $this->expectException(ESLException::class);
        Request::parse('bogus param0 paramB');
    }

    public function testParseEmpty(): void
    {
        $this->expectException(ESLException::class);
        Request::parse(' ');
    }

    public function testParseWhitespace(): void
    {
        $this->expectException(ESLException::class);
        Request::parse(' ');
    }

    public function testRender(): void
    {
        $this->assertEquals("default\n\n", $this->request->render());

        $this->request->setparameters('paramA');

        $this->assertEquals("default paramA\n\n", $this->request->render());

        $this->request->setHeader('header', 'value');

        $this->assertEquals("default paramA\nheader: value\n\n", $this->request->render());

        $this->request->setBody('+OK');

        $this->assertEquals("default paramA\nheader: value\nContent-Length: 3\n\n+OK\n\n", $this->request->render());
    }
}
