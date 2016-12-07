<?php

/*
 * This file is part of vaibhavpandeyvpz/phemail package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.md.
 */

namespace Phemail\tests;

use Phemail\MessageParser;
use Phemail\MessageParserInterface;

/**
 * Class MessageParserTest
 * @package Phemail\Tests
 */
class MessageParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MessageParserInterface
     */
    protected $parser;

    public function setUp()
    {
        $this->parser = new MessageParser();
    }

    public function testPlainEmail()
    {
        $payload = file_get_contents(__DIR__ . '/../sample/plain.eml');
        $message = $this->parser->parse($payload);
        $this->assertTrue($message->isText());
        $this->assertFalse($message->isMultiPart());
        $this->assertEquals('1.0', $message->getHeaderValue('mime-version'));
        $this->assertEquals('Testing simple email', $message->getHeaderValue('subject'));
        $this->assertEquals('text/plain', $message->getHeaderValue('content-type'));
        $this->assertEquals('US-ASCII', $message->getHeaderAttribute('content-type', 'charset'));
        $this->assertEquals('7bit', $message->getHeaderValue('content-transfer-encoding'));
        $this->assertNotEmpty($contents = $message->getContents());
        $this->assertInternalType('string', $contents);
    }

    public function testMultiPartEmail()
    {
        $payload = file_get_contents(__DIR__ . '/../sample/multipart.eml');
        $message = $this->parser->parse($payload);
        $this->assertTrue($message->isMultiPart());
        $this->assertFalse($message->isText());
        $this->assertEquals('1.0', $message->getHeaderValue('mime-version'));
        $this->assertEquals('Testing multipart email', $message->getHeaderValue('subject'));
        $this->assertEquals('multipart/mixed', $message->getHeaderValue('content-type'));
        $this->assertEquals('652b8c4dcb00cdcdda1e16af36781caf', $message->getHeaderAttribute('content-type', 'boundary'));
        $this->assertEmpty($contents = $message->getContents());
        $this->assertCount(1, $attachments = $message->getAttachments());
        $this->assertFalse($attachments[0]->isMultiPart());
        $this->assertEquals('text/x-ruby-script', $attachments[0]->getHeaderValue('content-type'));
        $this->assertEquals('hello.rb', $attachments[0]->getHeaderAttribute('content-type', 'name'));
        $this->assertEquals('api.rb', $attachments[0]->getHeaderAttribute('content-disposition', 'filename'));
        $this->assertCount(2, $parts = $message->getParts());
        $this->assertTrue($parts[0]->isText());
        $this->assertFalse($parts[0]->isMultiPart());
        $this->assertEquals('text/plain', $parts[0]->getHeaderValue('content-type'));
        $this->assertEquals('US-ASCII', $parts[0]->getHeaderAttribute('content-type', 'charset'));
        $this->assertEquals('yes', $parts[0]->getHeaderAttribute('content-type', 'delsp'));
        $this->assertEquals('flowed', $parts[0]->getHeaderAttribute('content-type', 'format'));
        $this->assertEquals('7bit', $parts[0]->getHeaderValue('content-transfer-encoding'));
        $this->assertNotEmpty($contents = $parts[0]->getContents());
        $this->assertInternalType('string', $contents);
        $this->assertTrue($parts[1]->isText());
        $this->assertFalse($parts[1]->isMultiPart());
        $this->assertEquals('text/html', $parts[1]->getHeaderValue('content-type'));
        $this->assertEquals('ISO-8859-1', $parts[1]->getHeaderAttribute('content-type', 'charset'));
        $this->assertEquals('flowed', $parts[1]->getHeaderAttribute('content-type', 'format'));
        $this->assertEquals('quoted-printable', $parts[1]->getHeaderValue('content-transfer-encoding'));
        $this->assertNotEmpty($contents = $parts[1]->getContents());
        $this->assertInternalType('string', $contents);
    }
}
