<?php

declare(strict_types = 1);

namespace RTCKit\ESL\Tests;

use PHPUnit\Framework\TestCase;

use RTCKit\ESL\{
    Message,
    MessageInterface
};

/**
 * Class MessageTest.
 *
 * @covers \RTCKit\ESL\Message
 */
class MessageTest extends TestCase
{
    private Message $message;

    public function setUp(): void
    {
        $this->message = new Message;
    }

    public function testSetHeaders(): void
    {
        $headers = ['test' => 'value'];

        $ret = $this->message->setHeaders($headers);

        $this->assertInstanceOf(MessageInterface::class, $ret);
        $this->assertInstanceOf(Message::class, $ret);

        $ref = new \ReflectionProperty($this->message, 'headers');
        $ref->setAccessible(true);
        $value = $ref->getValue($this->message);

        $this->assertIsArray($value);
        $this->assertEquals($headers, $value);
    }

    public function testGetHeaders(): void
    {
        $headers = ['test' => 'value'];

        $this->message->setHeaders($headers);
        $ref = new \ReflectionProperty($this->message, 'headers');
        $ref->setAccessible(true);
        $ref->setValue($this->message, $headers);

        $value = $this->message->getHeaders();

        $this->assertIsArray($value);
        $this->assertEquals($headers, $value);
    }

    public function testSetHeader(): void
    {
        $this->message->setHeaders([
            'header1' => 'value1',
            'header2' => 'value2',
        ]);

        $ret = $this->message->setHeader('new', 'value');

        $this->assertInstanceOf(MessageInterface::class, $ret);
        $this->assertInstanceOf(Message::class, $ret);

        $ref = new \ReflectionProperty($this->message, 'headers');
        $ref->setAccessible(true);
        $value = $ref->getValue($this->message);

        $this->assertArrayHasKey('new', $value);
        $this->assertEquals('value', $value['new']);
        $this->assertEquals('value1', $value['header1']);

        $this->message->setHeader('header1', 'overwritten');

        $value = $ref->getValue($this->message);

        $this->assertNotEquals('value1', $value['header1']);
        $this->assertEquals('overwritten', $value['header1']);
    }

    public function testGetHeader(): void
    {
        $headers = ['test' => 'value'];

        $this->message->setHeaders($headers);
        $ref = new \ReflectionProperty($this->message, 'headers');
        $ref->setAccessible(true);
        $ref->setValue($this->message, $headers);

        $value = $this->message->getHeader('test');

        $this->assertNotNull($value);
        $this->assertIsString($value);
        $this->assertEquals('value', $value);

        $null = $this->message->getHeader('bogus');
        $this->assertNull($null);
    }

    public function testSetBody(): void
    {
        $body = '+OK Alright';

        $ret = $this->message->setBody($body);

        $this->assertInstanceOf(MessageInterface::class, $ret);
        $this->assertInstanceOf(Message::class, $ret);

        $ref = new \ReflectionProperty($this->message, 'body');
        $ref->setAccessible(true);
        $value = $ref->getValue($this->message);

        $this->assertIsString($value);
        $this->assertEquals($body, $value);
    }

    public function testGetBody(): void
    {
        $body = '+OK Alright';

        $this->message->setBody($body);
        $ref = new \ReflectionProperty($this->message, 'body');
        $ref->setAccessible(true);
        $ref->setValue($this->message, $body);

        $value = $this->message->getBody();

        $this->assertIsString($value);
        $this->assertEquals($body, $value);

        $new = new Message;
        $ref = new \ReflectionProperty($new, 'body');
        $ref->setAccessible(true);

        $this->assertFalse($ref->isInitialized($new));
        $this->assertNull($new->getBody());
    }

    public function testRenderHeaders(): void
    {
        $this->assertEquals('', $this->message->renderHeaders());

        $this->message->setHeaders([
            'header1' => 'value1',
            'header2' => 'value2',
        ]);

        $headers = $this->message->renderHeaders();

        $this->assertIsString($headers);
        $this->assertEquals("header1: value1\nheader2: value2", $headers);
    }

    public function testRender(): void
    {
        $this->message->setHeaders([
            'header1' => 'value1',
            'header2' => 'value2',
        ]);

        $rendered = $this->message->render();
        $this->assertIsString($rendered);
        $this->assertEquals("header1: value1\nheader2: value2\n\n", $rendered);

        $this->message->setBody('+OK');

        $rendered = $this->message->render();
        $this->assertIsString($rendered);
        $this->assertEquals("header1: value1\nheader2: value2\nContent-Length: 3\n\n+OK\n\n", $rendered);
    }

    public function testParseHeadersBody(): void
    {
        $headers = Message::parseHeadersBody("header1: value1\nheader2: value2\nContent-Length: 3\n\n+OK\n\n", $body);

        $this->assertEquals(['header1' => 'value1', 'header2' => 'value2', 'content-length' => '3'], $headers);
        $this->assertEquals('+OK', $body);

        $headers = Message::parseHeadersBody("header1: value1\nheader2: value2\n\n", $body);

        $this->assertEquals(['header1' => 'value1', 'header2' => 'value2'], $headers);
        $this->assertEquals('', $body);
    }
}
