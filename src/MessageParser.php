<?php

/*
 * This file is part of vaibhavpandeyvpz/phemail package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Phemail;

use Phemail\Message\Header;
use Phemail\Message\MessagePart;

/**
 * Class MessageParser
 */
class MessageParser implements MessageParserInterface
{
    const REGEX_HEADER_LINE = '~^(?![\s]+)(?<name>[^:]+):(\s+(?<content>(?<value>[^;]+).*))?$~';

    const REGEX_HEADER_LINE_EXTENDED = '~^\s+(?<content>.*)$~';

    const REGEX_ATTRIBUTE = '~[;\s]+(?<name>[^=]+)=(?:["])?(?<value>[^;"]+)(?:["])?~';

    /*
     * {@inheritdoc}
     */
    public function parse($payload, $withSubMesssage = true)
    {
        if (is_string($payload)) {
            $iterator = new \ArrayIterator(file($payload, FILE_IGNORE_NEW_LINES));
        } elseif (is_array($payload)) {
            $iterator = new \ArrayIterator($payload);
        } elseif ($payload instanceof \Iterator) {
            $iterator = $payload;
        } else {
            throw new \InvalidArgumentException('$payload must be either string, array or an instance of \\Iterator');
        }
        $message = $this->parseHeaders($iterator, $message = new MessagePart);
        $message = $this->parseMessage($iterator, $message, null, $withSubMesssage);

        return $message;
    }

    /**
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
                // Initialize content and value if they don't exist (header with no value on first line)
                if (! isset($matches['content'])) {
                    $matches['content'] = '';
                }
                if (! isset($matches['value'])) {
                    $matches['value'] = '';
                }

                // Process continuation lines (folded headers)
                while ($lines->valid()) {
                    $lines->next();
                    if (! $lines->valid()) {
                        break;
                    }
                    $line = $lines->current();
                    if (preg_match(self::REGEX_HEADER_LINE_EXTENDED, $line, $matches2)) {
                        // Append continuation line content
                        $matches['content'] .= ($matches['content'] ? ' ' : '').trim($matches2['content']);
                        // Update value if it's the first continuation line and value was empty
                        if (empty($matches['value']) && ! empty($matches2['content'])) {
                            $matches['value'] = trim($matches2['content']);
                        }

                        continue;
                    }
                    break;
                }
                $matches['name'] = strtolower($matches['name']);
                $header = new Header;

                switch ($matches['name']) {
                    case 'content-disposition':
                    case 'content-type':
                        // Use value if available, otherwise use content
                        $headerValue = ! empty($matches['value']) ? $matches['value'] : $matches['content'];
                        $header = $header->withValue($headerValue);
                        if (! empty($matches['content']) && preg_match_all(self::REGEX_ATTRIBUTE, $matches['content'], $attributes)) {
                            foreach ($attributes['name'] as $i => $attribute) {
                                $header = $header->withAttribute(trim($attribute), $attributes['value'][$i]);
                            }
                        }
                        break;
                    default:
                        $header = $header->withValue($matches['content'] ?? '');
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
     * @param  bool  $withSubMesssage
     * @param  bool  $parseSubMessage
     * @return MessagePart
     */
    protected function parseMessage(\Iterator $lines, MessagePart $part, $boundary = null, $withSubMesssage = true, $parseSubMessage = true, $parentBoundary = null)
    {
        if ($part->isMultiPart()) {
            // Get this multipart's boundary
            $multipartBoundary = $part->getHeaderAttribute('content-type', 'boundary');
            if ($multipartBoundary === null) {
                return $part;
            }

            $boundaryStart = "--$multipartBoundary";
            $boundaryEnd = "--$multipartBoundary--";
            $parentBoundaryStart = $parentBoundary ? "--$parentBoundary" : null;
            $parentBoundaryEnd = $parentBoundary ? "--$parentBoundary--" : null;

            // Capture preamble (text before first boundary) - RFC 2046
            $preamble = [];
            while ($lines->valid()) {
                $line = $lines->current();
                $trimmed = trim($line);

                if ($trimmed === $boundaryStart || $trimmed === $boundaryEnd) {
                    break;
                }

                $preamble[] = $line;
                $lines->next();
            }

            if (! empty($preamble)) {
                $part = $part->withContents(implode(PHP_EOL, $preamble));
            }

            // Parse multipart parts
            while ($lines->valid()) {
                $line = trim($lines->current());

                // Check if this is a part boundary
                if ($line === $boundaryStart) {
                    // Found a part - advance past boundary and parse it
                    $lines->next();
                    $sub = $this->parseHeaders($lines, new MessagePart);
                    // Pass this multipart's boundary as parentBoundary for nested multiparts
                    // Pass multipartBoundary as boundary so non-multipart parts know when to stop
                    $sub = $this->parseMessage($lines, $sub, $multipartBoundary, $withSubMesssage, $withSubMesssage, $multipartBoundary);
                    $part = $part->withPart($sub);

                    // After parsing, check if we're at the next boundary (don't advance)
                    // The loop will check it on the next iteration
                    continue;
                }

                // Check if this is the final boundary
                if ($line === $boundaryEnd) {
                    // Found final boundary - advance past it
                    $lines->next();

                    // Capture epilogue (text after final boundary) - RFC 2046
                    // But stop if we hit a parent boundary (for nested multiparts)
                    $epilogue = [];
                    while ($lines->valid()) {
                        $currentLine = trim($lines->current());

                        // Stop if we hit a parent boundary
                        if ($parentBoundaryStart && ($currentLine === $parentBoundaryStart || $currentLine === $parentBoundaryEnd)) {
                            break;
                        }

                        $epilogue[] = $lines->current();
                        $lines->next();
                    }

                    if (! empty($epilogue)) {
                        $currentContent = $part->getContents();
                        $epilogueText = implode(PHP_EOL, $epilogue);
                        $part = $part->withContents(
                            $currentContent.($currentContent ? PHP_EOL : '').$epilogueText
                        );
                    }

                    // Done with this multipart
                    break;
                }

                // Not a boundary - advance to next line
                $lines->next();
            }

            return $part;
        } elseif ($part->isMessage() && $parseSubMessage) {
            // Skip blank line after message header
            $lines->next();
            $sub = $this->parseHeaders($lines, new MessagePart);
            // Pass parent boundary to nested message (boundary is for content parsing)
            $sub = $this->parseMessage($lines, $sub, $parentBoundary, $withSubMesssage, $withSubMesssage, $parentBoundary);

            return $part->withPart($sub);
        } else {
            // Non-multipart part - parse content until we hit a boundary
            if ($part->isMessage()) {
                $lines->next();
            }

            // Use boundary to stop at parent boundaries (for nested parts)
            // If boundary is null, parse until end (for top-level parts)
            return $part->withContents($this->parseContent($lines, $boundary));
        }
    }

    /**
     * @return string
     */
    protected function parseContent(\Iterator $lines, $boundary)
    {
        $contents = [];
        while ($lines->valid()) {
            $line = $lines->current();
            $trimmed = trim($line);
            if (is_null($boundary) || ($trimmed !== "--$boundary" && $trimmed !== "--$boundary--")) {
                $contents[] = $line;
            } else {
                break;
            }
            $lines->next();
        }

        return implode(PHP_EOL, $contents);
    }
}
