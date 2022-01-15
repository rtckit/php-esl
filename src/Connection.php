<?php
/**
 * RTCKit\ESL\Connection Class
 */
declare(strict_types = 1);

namespace RTCKit\ESL;

use RTCKit\ESL\Exception\ESLException;

/**
 * ESL connection class
 */
class Connection implements ConnectionInterface
{
    public int $role;

    public string $buffer = '';

    private MessageInterface $message;

    /**
     * Constructs a new connection
     *
     * @param int $role
     */
    public function __construct(int $role)
    {
        $this->role = $role;
    }

    /**
     * Consumes incoming ESL traffic (raw bytes)
     *
     * @param string $chunk
     * @param list<MessageInterface> $messages
     * @return int
     */
    public function consume(string $chunk, ?array &$messages = []): int
    {
        /** @var int */
        $status = self::READY;

        /** @var list<MessageInterface> */
        $messages = [];

        if (!isset($chunk[0])) {
            /* Nothing to consume! */
            return $status;
        }

        if (!isset($this->buffer[0])) {
            $this->buffer = $chunk;
        } else {
            $this->buffer .= $chunk;
        }

        do {
            if (!isset($this->message)) {
                /* We're waiting for new messages */
                $blocks = explode(MessageInterface::MESSAGE_SEPARATOR, $this->buffer, 2);

                if (count($blocks) === 1) {
                    /* We don't have a whole message just yet */
                    $status = self::WAIT_MESSAGE;

                    break;
                }

                if (($this->role === self::INBOUND_CLIENT) || ($this->role === self::OUTBOUND_SERVER)) {
                    $this->message = Response::parse($this->buffer);
                } else {
                    $this->message = Request::parse($this->buffer);
                }

                $this->buffer = $blocks[1];
            } else {
                /* We are waiting for the remainder of the body of the current message */
            }

            $bodyLength = strlen($this->buffer);
            $contentLength = $this->message->getHeader(AbstractHeader::CONTENT_LENGTH);

            if (isset($contentLength)) {
                $contentLength = (int)$contentLength;

                if ($bodyLength < $contentLength) {
                    /* We need to wait for more body bytes */
                    $status = self::WAIT_BODY;

                    break;
                } else if ($bodyLength > $contentLength) {
                    /* Move the remainder of bytes into the buffer and save the body */
                    $this->message->setBody(substr($this->buffer, 0, $contentLength));
                    $this->buffer = ltrim(substr($this->buffer, $contentLength));
                } else {
                    /* We're all set! */
                    $this->message->setBody($this->buffer);
                    $this->buffer = '';
                }
            } else {
                $this->message->setBody('');
            }

            $messages[] = $this->message;

            unset($this->message);

            $status = self::SUCCESS;
        } while ($status === self::SUCCESS);

        return (isset($messages[0])) ? self::SUCCESS : $status;
    }

    /**
     * Initiates the sending of an outgoing ESL message
     *
     * @param MessageInterface $message
     */
    public function emit(MessageInterface $message): void
    {
        $this->emitBytes($message->render());
    }

    /**
     * Performs the actual sending of an ESL message.
     * The library handling the I/O must properly implement this method.
     *
     * @param string $bytes
     */
    protected function emitBytes(string $bytes): void
    {
        throw new ESLException(get_class($this) . '::emitBytes() not implemented');
    }
}
