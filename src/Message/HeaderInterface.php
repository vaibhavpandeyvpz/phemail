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
 * Interface HeaderInterface
 * @package Phemail\Message
 */
interface HeaderInterface
{
    /**
     * @return string
     */
    public function getValue();

    /**
     * @return string[]
     */
    public function getAttributes();

    /**
     * @param string $name
     * @return string
     */
    public function getAttribute($name);
}
