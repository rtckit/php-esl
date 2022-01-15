<?php
/**
 * RTCKit\ESL\Message Class
 */
declare(strict_types = 1);

namespace RTCKit\ESL;

/**
 * Base ESL message class
 */
class Message implements MessageInterface
{
    /** @var array<string, string> */
    protected array $headers = [];

    protected string $body;

    /**
     * Sets message's headers
     *
     * @param array<string, string> $headers
     * @return static
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Gets message's headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Sets an individual message header
     *
     * @param string $name
     * @param string $value
     * @return static
     */
    public function setHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Retrieves a specific message header
     *
     * @param string $name
     *
     * @return ?string
     */
    public function getHeader(string $name): ?string
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return null;
    }

    /**
     * Sets message's body
     *
     * @param string $body
     * @return static
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Retrieves message's body
     *
     * @return ?string
     */
    public function getBody(): ?string
    {
        if (isset($this->body)) {
            return $this->body;
        }

        return null;
    }

    /**
     * Renders message's headers as string
     *
     * @return string
     */
    public function renderHeaders(): string
    {
        if (empty($this->headers)) {
            return '';
        }

        $ret = [];

        foreach ($this->headers as $name => $value) {
            $ret[] = $name . self::HEADER_TUPLE_CANONICAL_SEPARATOR . $value;
        }

        return implode(self::HEADER_SEPARATOR, $ret);
    }

    /**
     * Renders the ESL message as string
     *
     * @return string
     */
    public function render(): string
    {
        if (isset($this->body)) {
            if (!isset($this->headers[AbstractHeader::CONTENT_LENGTH])) {
                $this->headers[AbstractHeader::CANONICAL[AbstractHeader::CONTENT_LENGTH]] = (string)strlen($this->body);
            }

            return $this->renderHeaders() . self::BODY_SEPARATOR . $this->body . self::MESSAGE_SEPARATOR;
        }

        return $this->renderHeaders() . self::MESSAGE_SEPARATOR;
    }

    /**
     * Parses raw bytes into message headers and body
     *
     * @return array<string, string>
     */
    public static function parseHeadersBody(string $input, ?string &$body): array
    {
        $headers = [];
        $body = null;
        $parts = explode(self::BODY_SEPARATOR, $input);
        $rawHeaders = explode(self::HEADER_SEPARATOR, rtrim($parts[0]));

        foreach ($rawHeaders as $rawHeader) {
            $headerParts = explode(self::HEADER_TUPLE_SEPARATOR, $rawHeader, 2);
            $headers[strtolower($headerParts[0])] = isset($headerParts[1]) ? ltrim($headerParts[1]) : '';
        }

        if (isset($parts[1])) {
            $body = $parts[1];
        }

        return $headers;
    }
}
