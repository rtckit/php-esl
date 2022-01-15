<?php
/**
 * RTCKit\ESL\Request Class
 */
declare(strict_types = 1);

namespace RTCKit\ESL;

use RTCKit\ESL\Exception\ESLException;

/**
 * Base ESL request class
 */
class Request extends Message implements MessageInterface, RequestInterface
{
    public const REQUEST_LINE_SEPARATOR = "\n";

    public const COMMAND_SEPARATOR = ' ';

    public const COMMAND = 'default';

    protected string $parameters;

    /**
     * Constructs a new request
     *
     * @param ?string $parameters
     */
    public function __construct(?string $parameters = null)
    {
        if (isset($parameters)) {
            $this->parameters = $parameters;
        }
    }

    /**
     * Sets request's parameters
     *
     * @param string $parameters
     * @return static
     */
    public function setParameters(string $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Retrieves request's parameters
     *
     * @return ?string
     */
    public function getParameters(): ?string
    {
        if (!isset($this->parameters)) {
            return null;
        }

        return $this->parameters;
    }

    /**
     * Parses framed raw bytes into well formed ESL request
     *
     * @param string $bytes
     * @return MessageInterface
     */
    public static function parse(string $bytes): MessageInterface
    {
        $parts = explode(self::REQUEST_LINE_SEPARATOR, $bytes, 2);
        $requestLine = trim($parts[0]);
        $rawHeadersBody = isset($parts[1]) ? $parts[1] : null;

        if (!isset($requestLine[0])) {
            throw new ESLException('Empty request line');
        }

        $parts = explode(self::COMMAND_SEPARATOR, $requestLine, 2);
        $command = $parts[0];

        switch ($command) {
            case Request\Api::COMMAND:
                $ret = new Request\Api;
                break;

            case Request\Auth::COMMAND:
                $ret = new Request\Auth;
                break;

            case Request\BgApi::COMMAND:
                $ret = new Request\BgApi;
                break;

            case Request\Connect::COMMAND:
                $ret = new Request\Connect;
                break;

            case Request\DivertEvents::COMMAND:
                $ret = new Request\DivertEvents;
                break;

            case Request\Eksit::COMMAND:
                $ret = new Request\Eksit;
                break;

            case Request\Event::COMMAND:
                $ret = new Request\Event;
                break;

            case Request\Filter::COMMAND:
                $ret = new Request\Filter;
                break;

            case Request\Linger::COMMAND:
                $ret = new Request\Linger;
                break;

            case Request\Log::COMMAND:
                $ret = new Request\Log;
                break;

            case Request\MyEvents::COMMAND:
                $ret = new Request\MyEvents;
                break;

            case Request\NixEvent::COMMAND:
                $ret = new Request\NixEvent;
                break;

            case Request\NoEvents::COMMAND:
                $ret = new Request\NoEvents;
                break;

            case Request\NoLinger::COMMAND:
                $ret = new Request\NoLinger;
                break;

            case Request\NoLog::COMMAND:
                $ret = new Request\NoLog;
                break;

            case Request\Resume::COMMAND:
                $ret = new Request\Resume;
                break;

            case Request\SendEvent::COMMAND:
                $ret = new Request\SendEvent;
                break;

            case Request\SendMsg::COMMAND:
                $ret = new Request\SendMsg;
                break;

            default:
                throw new ESLException('Unknown request');
        }

        if (isset($parts[1])) {
            $ret->setParameters($parts[1]);
        }

        if (!isset($rawHeadersBody, $rawHeadersBody[0])) {
            return $ret;
        }

        $ret->setHeaders(parent::parseHeadersBody($rawHeadersBody, $body));

        if (isset($body)) {
            $ret->setBody($body);
        }

        return $ret;
    }

    /**
     * Renders the ESL request as string
     *
     * @return string
     */
    public function render(): string
    {
        if (isset($this->parameters)) {
            $parameters = self::COMMAND_SEPARATOR . $this->parameters;
        } else {
            $parameters = '';
        }

        if (empty($this->headers) && empty($this->body)) {
            return static::COMMAND . $parameters . self::MESSAGE_SEPARATOR;
        }

        return static::COMMAND . $parameters . self::REQUEST_LINE_SEPARATOR . parent::render();
    }
}
