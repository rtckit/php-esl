<?php
/**
 * RTCKit\ESL\RequestInterface Interface
 */
declare(strict_types = 1);

namespace RTCKit\ESL;

/**
 * ESL request interface
 */
interface RequestInterface
{
    /**
     * Constructs a new request
     *
     * @param ?string $parameters
     */
    public function __construct(?string $parameters = null);

    /**
     * Sets request's parameters
     *
     * @param string $parameters
     * @return static
     */
    public function setParameters(string $parameters);

    /**
     * Retrieves request's parameters
     *
     * @return ?string
     */
    public function getParameters(): ?string;

    /**
     * Parses framed raw bytes into well formed ESL request
     *
     * @param string $bytes
     * @return MessageInterface
     */
    public static function parse(string $bytes): MessageInterface;

    /**
     * Renders the ESL request as string
     *
     * @return string
     */
    public function render(): string;
}
