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
 * Interface MessagePartInterface
 * @package Phemail\Message
 */
interface MessagePartInterface
{
    /**
     * @return HeaderInterface[]
     */
    public function getHeaders();

    /**
     * @param string $name
     * @return HeaderInterface
     */
    public function getHeader($name);

    /**
     * @param string $header
     * @param string $attr
     * @param string $default
     * @return string
     */
    public function getHeaderAttribute($header, $attr, $default = null);

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getHeaderValue($name, $default = null);

    /**
     * @return bool
     */
    public function isMultiPart();

    /**
     * @return bool
     */
    public function isText();

    /**
     * @return string
     */
    public function getContentType();

    /**
     * @return string
     */
    public function getContents();

    /**
     * @return MessagePartInterface[]
     */
    public function getAttachments();

    /**
     * @return MessagePartInterface[]
     */
    public function getParts();
}
