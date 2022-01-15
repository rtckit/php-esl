<?php

declare(strict_types = 1);

namespace RTCKit\ESL\Tests;

use PHPUnit\Framework\TestCase;

use RTCKit\ESL\{
    Message,
    MessageInterface,
    Response,
    ResponseInterface
};
use RTCKit\ESL\Exception\ESLException;

/**
 * Class ResponseTest.
 *
 * @covers \RTCKit\ESL\Response
 */
class ResponseTest extends TestCase
{
    private Response $response;

    public function setUp(): void
    {
        $this->response = new Response;
    }

    public function testParseWithoutBody(): void
    {
        $response = Response::parse(
            "Content-Type: command/reply\n" .
            "Reply-Text: +OK Job-UUID: d8c7f660-37a6-4e73-9170-1a731c442148\n" .
            "Job-UUID: d8c7f660-37a6-4e73-9170-1a731c442148\n"
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(MessageInterface::class, $response);
        $this->assertInstanceOf(Message::class, $response);

        $headers = $response->getHeaders();

        $this->assertEquals('command/reply', $headers['content-type']);
        $this->assertEquals('+OK Job-UUID: d8c7f660-37a6-4e73-9170-1a731c442148', $headers['reply-text']);
        $this->assertEquals('d8c7f660-37a6-4e73-9170-1a731c442148', $headers['job-uuid']);
        $this->assertEmpty($response->getBody());
    }

    public function testParseWithBody(): void
    {
        $response = Response::parse(
            "Content-Type: text/event-plain\n" .
            "Job-UUID: 7f4db78a-17d7-11dd-b7a0-db4edd065621\n" .
            "Job-Command: originate\n" .
            "Job-Command-Arg: sofia/default/1005%20'%26park'\n" .
            "Event-Name: BACKGROUND_JOB\n" .
            "Core-UUID: 42bdf272-16e6-11dd-b7a0-db4edd065621\n" .
            "FreeSWITCH-Hostname: ser\n" .
            "FreeSWITCH-IPv4: 192.168.1.104\n" .
            "FreeSWITCH-IPv6: 127.0.0.1\n" .
            "Event-Date-Local: 2008-05-02%2007%3A37%3A03\n" .
            "Event-Date-GMT: Thu,%2001%20May%202008%2023%3A37%3A03%20GMT\n" .
            "Event-Date-timestamp: 1209685023894968\n" .
            "Event-Calling-File: mod_event_socket.c\n" .
            "Event-Calling-Function: api_exec\n" .
            "Event-Calling-Line-Number: 609\n" .
            "Content-Length: 41\n" .
            "\n" .
            "+OK 7f4de4bc-17d7-11dd-b7a0-db4edd065621\n"
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(MessageInterface::class, $response);
        $this->assertInstanceOf(Message::class, $response);

        $this->assertEquals("+OK 7f4de4bc-17d7-11dd-b7a0-db4edd065621\n", $response->getBody());
    }

    public function testParseByResponseType(): void
    {
        $this->assertInstanceOf(Response\ApiResponse::class, Response::parse('Content-Type: api/response'));
        $this->assertInstanceOf(Response\AuthRequest::class, Response::parse('Content-Type: auth/request'));
        $this->assertInstanceOf(Response\CommandReply::class, Response::parse('Content-Type: command/reply'));
        $this->assertInstanceOf(Response\LogData::class, Response::parse('Content-Type: log/data'));
        $this->assertInstanceOf(Response\TextDisconnectNotice::class, Response::parse('Content-Type: text/disconnect-notice'));
        $this->assertInstanceOf(Response\TextEventJson::class, Response::parse('Content-Type: text/event-json'));
        $this->assertInstanceOf(Response\TextEventPlain::class, Response::parse('Content-Type: text/event-plain'));
        $this->assertInstanceOf(Response\TextEventXml::class, Response::parse('Content-Type: text/event-xml'));
        $this->assertInstanceOf(Response\TextRudeRejection::class, Response::parse('Content-Type: text/rude-rejection'));

        $this->expectException(ESLException::class);
        Response::parse('Content-Type: bogus/mime-type');
    }

    public function testParseFailureWithoutContentType(): void
    {
        $this->expectException(ESLException::class);
        Response::parse(
            "Reply-Text: +OK Job-UUID: d8c7f660-37a6-4e73-9170-1a731c442148\n" .
            "Job-UUID: d8c7f660-37a6-4e73-9170-1a731c442148\n"
        );
    }

    public function testRender(): void
    {
        $this->assertEquals("Content-Type: default\n\n", $this->response->render());

        $this->response->setHeader('header', 'value');

        $this->assertEquals("Content-Type: default\nheader: value\n\n", $this->response->render());

        $this->response->setBody('+OK');

        $this->assertEquals("Content-Type: default\nheader: value\nContent-Length: 3\n\n+OK\n\n", $this->response->render());
    }

    public function testIsSuccessful(): void
    {
        $replyTextOK = Response::parse(
            "Content-Type: command/reply\n" .
            "Reply-Text: +OK Job-UUID: d8c7f660-37a6-4e73-9170-1a731c442148\n" .
            "Job-UUID: d8c7f660-37a6-4e73-9170-1a731c442148\n"
        );

        $this->assertTrue($replyTextOK->isSuccessful());

        $bodyTextOK = Response::parse(
            "Content-Type: text/event-plain\n" .
            "Job-UUID: 7f4db78a-17d7-11dd-b7a0-db4edd065621\n" .
            "Job-Command: originate\n" .
            "Job-Command-Arg: sofia/default/1005%20'%26park'\n" .
            "Content-Length: 41\n" .
            "\n" .
            "+OK 7f4de4bc-17d7-11dd-b7a0-db4edd065621\n"
        );

        $this->assertTrue($bodyTextOK->isSuccessful());

        $replyTextERR = Response::parse(
            "Content-Type: command/reply\n" .
            "Content-Length: 14\n\n" .
            "-ERR no reply\n"
        );

        $this->assertFalse($replyTextERR->isSuccessful());

        $inconclusive = Response::parse("Content-Type: api/response\n");

        $this->assertNull($inconclusive->isSuccessful());
    }
}
