<?php
/**
 * RTCKit\ESL\MessageInterface Interface
 */
declare(strict_types = 1);

namespace RTCKit\ESL;

/**
 * ESL message interface
 */
interface MessageInterface
{
    public const MESSAGE_SEPARATOR = "\n\n";

    public const BODY_SEPARATOR = "\n\n";

    public const HEADER_SEPARATOR = "\n";

    public const HEADER_TUPLE_SEPARATOR = ':';

    public const HEADER_TUPLE_CANONICAL_SEPARATOR = ': ';

    /**
     * Sets message's headers
     *
     * @param array<string, string> $headers
     * @return static
     */
    public function setHeaders(array $headers);

    /**
     * Gets message's headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): array;

    /**
     * Sets an individual message header
     *
     * @param string $name
     * @param string $value
     * @return static
     */
    public function setHeader(string $name, string $value);

    /**
     * Retrieves a specific message header
     *
     * @param string $name
     *
     * @return ?string
     */
    public function getHeader(string $name): ?string;

    /**
     * Sets message's body
     *
     * @param string $body
     * @return static
     */
    public function setBody(string $body);

    /**
     * Retrieves message's body
     *
     * @return ?string
     */
    public function getBody(): ?string;

    /**
     * Renders message's headers as string
     *
     * @return string
     */
    public function renderHeaders(): string;

    /**
     * Renders the ESL message as string
     *
     * @return string
     */
    public function render(): string;

    /**
     * Parses raw bytes into message headers and body
     *
     * @return array<string, string>
     */
    public static function parseHeadersBody(string $input, ?string &$body): array;
}
