<?php

/*
 * This file is part of vaibhavpandeyvpz/phemail package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.md.
 */

namespace Phemail;

use Phemail\Message\Header;
use Phemail\Message\MessagePart;

/**
 * Class MessageParser
 * @package Phemail
 */
class MessageParser implements MessageParserInterface
{
    const REGEX_HEADER_LINE = '~^(?![\s]+)(?<name>[^:]+):\s+(?<content>(?<value>[^;]+).*)$~';

    const REGEX_HEADER_LINE_EXTENDED = '~^\s+(?<content>.*)$~';

    const REGEX_ATTRIBUTE = '~[;\s]+(?<name>[^=]+)=(?:["])?(?<value>[^;"]+)(?:["])?~';

    const REGEX_BOUNDARY = '~^[-]{2}(?<boundary>.*[^-]{2})(?<end>[-]{2})?$~';

    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        if (is_string($payload)) {
            $iterator = new \ArrayIterator(file($payload, FILE_IGNORE_NEW_LINES));
        } elseif (is_array($payload)) {
            $iterator = new \ArrayIterator($payload);
        } elseif ($payload instanceof \Iterator) {
            $iterator = $payload;
        } else {
            throw new \InvalidArgumentException("\$payload must be either string, array or an instance of \\Iterator");
        }
        $message = $this->parseHeaders($iterator, $message = new MessagePart());
        $message = $this->parseMessage($iterator, $message);
        return $message;
    }

    /**
     * @param \Iterator $lines
     * @param MessagePart $part
     * @return MessagePart
     */
    protected function parseHeaders(\Iterator $lines, MessagePart $part)
    {
        while ($lines->valid()) {
            $line = $lines->current();
            if (empty($line)) {
                break;
            }
            if (preg_match(self::REGEX_HEADER_LINE, $line, $matches)) {
                while ($lines->valid()) {
                    $lines->next();
                    $line = $lines->current();
                    if (preg_match(self::REGEX_HEADER_LINE_EXTENDED, $line, $matches2)) {
                        $matches['content'] .= " " . trim($matches2['content']);
                        continue;
                    }
                    break;
                }
                $matches['name'] = strtolower($matches['name']);
                $header = new Header();
                switch ($matches['name']) {
                    case 'content-disposition':
                    case 'content-type':
                        $header = $header->withValue($matches['value']);
                        if (preg_match_all(self::REGEX_ATTRIBUTE, $matches['content'], $attributes)) {
                            foreach ($attributes['name'] as $i => $attribute) {
                                $header = $header->withAttribute($attribute, $attributes['value'][$i]);
                            }
                        }
                        break;
                    default:
                        $header = $header->withValue($matches['content']);
                        break;
                }
                $part = $part->withHeader($matches['name'], $header);
            } else {
                $lines->next();
            }
        }
        return $part;
    }

    /**
     * @param \Iterator $lines
     * @param MessagePart $part
     * @return MessagePart
     */
    protected function parseMessage(\Iterator $lines, MessagePart $part)
    {
        while ($lines->valid()) {
            $line = $lines->current();
            $lines->next();
            if ($part->isMultiPart()) {
                if (preg_match(self::REGEX_BOUNDARY, $line, $matches)) {
                    if (array_key_exists('end', $matches)) {
                        break;
                    }
                    $sub = $this->parseHeaders($lines, $sub = new MessagePart());
                    $sub = $this->parseMessage($lines, $sub);
                    $part = $part->withPart($sub);
                }
            } else {
                return $part->withContents($this->parseContent($lines));
            }
        }
        return $part;
    }

    /**
     * @param \Iterator $lines
     * @return string
     */
    protected function parseContent(\Iterator $lines)
    {
        $contents = array();
        while ($lines->valid()) {
            $line = $lines->current();
            if (preg_match(self::REGEX_BOUNDARY, $line)) {
                break;
            } else {
                $contents[] = $line;
            }
            $lines->next();
        }
        return implode(PHP_EOL, $contents);
    }
}
