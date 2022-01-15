<?php

declare(strict_types = 1);

namespace RTCKit\ESL\Tests;

use PHPUnit\Framework\TestCase;
use RTCKit\ESL\{
    Connection,
    MessageInterface,
    Response,
    Request
};
use RTCKit\ESL\Exception\ESLException;

/**
 * Class ConnectionTest.
 *
 * @covers \RTCKit\ESL\Connection
 */
class ConnectionTest extends TestCase
{
    public function testConstructor(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new Connection;
    }

    public function testConstructorNoRole(): void
    {
        $new = new Connection(Connection::INBOUND_CLIENT);

        $this->assertNotNull($new);
        $this->assertEquals(Connection::INBOUND_CLIENT, $new->role);
    }

    public function testConsume(): void
    {
        $conn = new Connection(Connection::INBOUND_CLIENT);
        $this->assertEquals(
            Connection::SUCCESS,
            $conn->consume(
                "Content-Type: text/event-plain\n" .
                "Job-UUID: 7f4db78a-17d7-11dd-b7a0-db4edd065621\n" .
                "Job-Command: originate\n" .
                "Job-Command-Arg: sofia/default/1005%20'%26park'\n" .
                "Content-Length: 41\n" .
                "\n" .
                "+OK 7f4de4bc-17d7-11dd-b7a0-db4edd065621\n",
                $messages
            )
        );
        $this->assertInstanceOf(Response\TextEventPlain::class, $messages[0]);
    }

    public function testConsumeEmpty(): void
    {
        $conn = new Connection(Connection::INBOUND_CLIENT);
        $this->assertEquals(Connection::READY, $conn->consume('', $messages));
        $this->assertEquals([], $messages);
    }

    public function testConsumeChunks(): void
    {
        $conn = new Connection(Connection::INBOUND_SERVER);
        $this->assertEquals(Connection::WAIT_MESSAGE, $conn->consume("auth ClueCon\n", $messages));
        $this->assertEquals([], $messages);

        $this->assertEquals(Connection::SUCCESS, $conn->consume("\n", $messages));
        $this->assertInstanceOf(Request\Auth::class, $messages[0]);
    }

    public function testConsumePartial(): void
    {
        $conn = new Connection(Connection::INBOUND_CLIENT);
        $this->assertEquals(Connection::WAIT_MESSAGE, $conn->consume('Content-', $messages));
        $this->assertEquals([], $messages);

        $this->assertEquals(Connection::WAIT_MESSAGE, $conn->consume('Type', $messages));
        $this->assertEquals([], $messages);
    }

    public function testConsumePartialBody(): void
    {
        $conn = new Connection(Connection::INBOUND_CLIENT);
        $this->assertEquals(Connection::WAIT_BODY, $conn->consume("Content-Type: api/response\nContent-Length: 80\n\nabc...", $messages));
        $this->assertEquals([], $messages);
    }

    public function testConsumePartialBodyFollowedByAnotherMessage(): void
    {
        $conn = new Connection(Connection::INBOUND_CLIENT);
        $this->assertEquals(
            Connection::SUCCESS,
            $conn->consume("Content-Type: api/response\nContent-Length: 3\n\n+OKContent-Type: text/event-json", $messages)
        );
        $this->assertInstanceOf(Response\ApiResponse::class, $messages[0]);
        $this->assertEquals('Content-Type: text/event-json', $conn->buffer);
    }

    public function testConsumeRequests(): void
    {
        $conn = new Connection(Connection::OUTBOUND_CLIENT);
        $this->assertEquals(Connection::SUCCESS, $conn->consume("connect\n\n", $messages));
        $this->assertInstanceOf(Request\Connect::class, $messages[0]);
    }

    public function testEmit(): void
    {
        $message = new Response;

        $mock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['emitBytes'])
            ->getMock();

        $mock->expects($this->once())->method('emitBytes');

        $mock->emit($message);
    }

    public function testEmitBytes(): void
    {
        $conn = new Connection(Connection::INBOUND_CLIENT);
        $ref = new \ReflectionClass($conn);
        $emitBytes = $ref->getMethod('emitBytes');
        $emitBytes->setAccessible(true);

        $this->expectException(ESLException::class);
        $emitBytes->invokeArgs($conn, ['']);
    }


}
