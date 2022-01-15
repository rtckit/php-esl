<?php
/**
 * RTCKit\ESL\ConnectionInterface Interface
 */
declare(strict_types = 1);

namespace RTCKit\ESL;

/**
 * ESL connection interface
 */
interface ConnectionInterface
{
    /* ConnectionInterface::consume() return values */
    public const READY = 0;
    public const WAIT_MESSAGE = 1;
    public const WAIT_BODY = 2;
    public const SUCCESS = 3;

    /* https://github.com/rtckit/php-esl#esl-connection */
    public const INBOUND_CLIENT = 1;
    public const OUTBOUND_SERVER = 2;
    public const INBOUND_SERVER = 3;
    public const OUTBOUND_CLIENT = 4;

    /**
     * Constructs a new connection
     *
     * @param int $role
     */
    public function __construct(int $role);

    /**
     * Consumes incoming ESL traffic (raw bytes)
     *
     * @param string $chunk
     * @param list<MessageInterface> $messages
     * @return int
     */
    public function consume(string $chunk, ?array &$messages = []): int;

    /**
     * Initiates the sending of an outgoing ESL message
     *
     * @param MessageInterface $message
     */
    public function emit(MessageInterface $message): void;
}
