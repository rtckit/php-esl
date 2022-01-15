<?php
/**
 * RTCKit\ESL\Response Class
 */
declare(strict_types = 1);

namespace RTCKit\ESL;

use RTCKit\ESL\Exception\ESLException;

/**
 * Base ESL response class
 */
class Response extends Message implements MessageInterface, ResponseInterface
{
    public const CONTENT_TYPE = 'default';

    /**
     * Parses framed raw bytes into well formed ESL response
     *
     * @param string $bytes
     * @return MessageInterface
     */
    public static function parse(string $bytes): MessageInterface
    {
        $headers = parent::parseHeadersBody($bytes, $body);

        if (!isset($headers[AbstractHeader::CONTENT_TYPE])) {
            throw new ESLException('Content-type not set');
        }

        switch ($headers[AbstractHeader::CONTENT_TYPE]) {
            case Response\ApiResponse::CONTENT_TYPE:
                $ret = new Response\ApiResponse;
                break;

            case Response\AuthRequest::CONTENT_TYPE:
                $ret = new Response\AuthRequest;
                break;

            case Response\CommandReply::CONTENT_TYPE:
                $ret = new Response\CommandReply;
                break;

            case Response\LogData::CONTENT_TYPE:
                $ret = new Response\LogData;
                break;

            case Response\TextDisconnectNotice::CONTENT_TYPE:
                $ret = new Response\TextDisconnectNotice;
                break;

            case Response\TextEventJson::CONTENT_TYPE:
                $ret = new Response\TextEventJson;
                break;

            case Response\TextEventPlain::CONTENT_TYPE:
                $ret = new Response\TextEventPlain;
                break;

            case Response\TextEventXml::CONTENT_TYPE:
                $ret = new Response\TextEventXml;
                break;

            case Response\TextRudeRejection::CONTENT_TYPE:
                $ret = new Response\TextRudeRejection;
                break;

            default:
                throw new ESLException('Unknown response');
        }

        $ret->setHeaders($headers);

        if (isset($body)) {
            $ret->setBody($body);
        }

        return $ret;
    }

    /**
     * Renders the ESL response as string
     *
     * @return string
     */
    public function render(): string
    {
        if (!isset($this->headers[AbstractHeader::CONTENT_TYPE])) {
            $this->headers[AbstractHeader::CANONICAL[AbstractHeader::CONTENT_TYPE]] = static::CONTENT_TYPE;
        }

        return parent::render();
    }

    /**
     * Assesses whether the response is successful or not
     *
     * @return bool
     */
    public function isSuccessful(): ?bool
    {
        if (isset($this->headers[AbstractHeader::REPLY_TEXT]) && (strlen($this->headers[AbstractHeader::REPLY_TEXT]) >= 3)) {
            return strtolower(substr($this->headers[AbstractHeader::REPLY_TEXT], 1, 2)) === 'ok';
        } else if (isset($this->body) && (strlen($this->body) >= 3)) {
            return strtolower(substr($this->body, 1, 2)) === 'ok';
        }

        return null;
    }
}
