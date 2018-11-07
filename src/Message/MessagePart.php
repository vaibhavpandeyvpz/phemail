<?php

/*
 * This file is part of vaibhavpandeyvpz/phemail package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.md.
 */

namespace Phemail\Message;

/**
 * Class MessagePart
 * @package Phemail\Message
 */
class MessagePart implements MessagePartInterface
{
    /**
     * @var HeaderInterface[]
     */
    protected $headers = array();

    /**
     * @var string
     */
    protected $contents;

    /**
     * @var MessagePartInterface[]
     */
    protected $attachments = array();

    /**
     * @var MessagePartInterface[]
     */
    protected $parts = array();

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        return array_key_exists($name, $this->headers) ? $this->headers[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderAttribute($header, $attr, $default = null)
    {
        $header = $this->getHeader($header);
        if ($header && ($attribute = $header->getAttribute($attr))) {
            return $attribute;
        }
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderValue($name, $default = null)
    {
        $header = $this->getHeader($name);
        return $header ? $header->getValue() : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, HeaderInterface $header)
    {
        $clone = clone $this;
        $clone->headers[$name] = $header;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->getHeaderValue('content-type', 'text/plain');
    }

    /**
     * {@inheritdoc}
     */
    public function isMultiPart()
    {
        return stripos($this->getContentType(), 'multipart/') === 0;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isMessage()
    {
        return stripos($this->getContentType(), 'message/') === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isText()
    {
        return stripos($this->getContentType(), 'text/') === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param string $contents
     * @return static
     */
    public function withContents($contents)
    {
        $clone = clone $this;
        $clone->contents = $contents;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getParts($recursive=False)
    {
        $ret = $this->parts;
        if ($recursive)
            foreach($this->parts as $part)
                $ret = array_merge($ret, $part->getParts(true));
        return $ret;
    }

    /**
     * @param MessagePartInterface $part
     * @return static
     */
    public function withPart(MessagePartInterface $part)
    {
        $clone = clone $this;
        if ($part->getHeaderValue('content-disposition') === 'attachment') {
            $clone->attachments[] = $part;
        } else {
            $clone->parts[] = $part;
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments($recursive=False)
    {
        $ret = $this->attachments;
        if ($recursive)
            foreach ($this->parts as $part)
                $ret = array_merge($ret, $part->getAttachments(true));
        return $ret;
    }
    
}
