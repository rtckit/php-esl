<?php
/**
 * RTCKit\ESL\ResponseInterface Interface
 */
declare(strict_types = 1);

namespace RTCKit\ESL;

/**
 * ESL response interface
 */
interface ResponseInterface
{
    /**
     * Parses framed raw bytes into well formed ESL response
     *
     * @param string $bytes
     * @return MessageInterface
     */
    public static function parse(string $bytes): MessageInterface;

    /**
     * Renders the ESL response as string
     *
     * @return string
     */
    public function render(): string;

    /**
     * Assesses whether the response is successful or not
     *
     * @return bool
     */
    public function isSuccessful(): ?bool;
}
