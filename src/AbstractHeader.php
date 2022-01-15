<?php
/**
 * RTCKit\ESL\AbstractHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\ESL;

/**
 * ESL header abstract class
 */
abstract class AbstractHeader
{
    public const CONTENT_LENGTH = 'content-length';

    public const CONTENT_TYPE = 'content-type';

    public const REPLY_TEXT = 'reply-text';

    public const CANONICAL = [
        self::CONTENT_LENGTH => 'Content-Length',
        self::CONTENT_TYPE => 'Content-Type',
        self::REPLY_TEXT => 'Reply-Text',
    ];
}
