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

use Phemail\Message\MessagePartInterface;

/**
 * Interface MessageParserInterface
 * @package Phemail
 */
interface MessageParserInterface
{
    /**
     * @param string|array|\Iterator $payload
     * @return MessagePartInterface
     */
    public function parse($payload);
}
