# vaibhavpandeyvpz/phemail

A pure PHP MIME parser for parsing raw email files (.eml) with full support for multipart messages, nested structures, and RFC 2046 compliance.

[![Latest Version](https://img.shields.io/packagist/v/vaibhavpandeyvpz/phemail.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/phemail)
[![Downloads](https://img.shields.io/packagist/dt/vaibhavpandeyvpz/phemail.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/phemail)
[![PHP Version](https://img.shields.io/packagist/php-v/vaibhavpandeyvpz/phemail.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/phemail)
[![License](https://img.shields.io/packagist/l/vaibhavpandeyvpz/phemail.svg?style=flat-square)](LICENSE)

## Features

- ✅ **Pure PHP** - No external dependencies required
- ✅ **RFC 2046 Compliant** - Proper handling of multipart messages with boundaries, preamble, and epilogue
- ✅ **Nested Structures** - Supports deeply nested multipart messages and message/rfc822 types
- ✅ **Header Parsing** - Handles folded headers, continuation lines, and header attributes
- ✅ **Attachment Detection** - Automatically identifies attachments via Content-Disposition header
- ✅ **Multiple Input Types** - Accepts file paths, arrays, or iterators
- ✅ **Immutable Objects** - Thread-safe design with immutable message parts
- ✅ **PHP 8.2+** - Modern PHP with strict types and latest language features

## Requirements

- PHP 8.2 or higher

## Installation

```bash
composer require vaibhavpandeyvpz/phemail
```

## Basic Usage

### Parsing a Simple Email

```php
<?php

use Phemail\MessageParser;

$parser = new MessageParser();
$message = $parser->parse('path/to/email.eml');

// Get header values
echo $message->getHeaderValue('subject');        // "Testing simple email"
echo $message->getHeaderValue('from');           // "sender@example.com"
echo $message->getHeaderValue('date');            // "Sat, 22 Nov 2008 15:04:59 +1100"

// Get header attributes
echo $message->getHeaderAttribute('content-type', 'charset');  // "US-ASCII"

// Get message content
echo $message->getContents();
```

### Parsing from Different Sources

```php
// From file path
$message = $parser->parse('/path/to/email.eml');

// From array of lines
$lines = file('email.eml', FILE_IGNORE_NEW_LINES);
$message = $parser->parse($lines);

// From iterator
$iterator = new \ArrayIterator($lines);
$message = $parser->parse($iterator);
```

## Advanced Usage

### Working with Multipart Messages

```php
$message = $parser->parse('multipart.eml');

// Check if message is multipart
if ($message->isMultiPart()) {
    echo "Content-Type: " . $message->getContentType();  // "multipart/mixed"

    // Get all parts
    $parts = $message->getParts();
    foreach ($parts as $part) {
        echo "Part: " . $part->getContentType() . "\n";
        echo "Content: " . $part->getContents() . "\n";
    }
}
```

### Extracting Attachments

```php
$message = $parser->parse('email-with-attachments.eml');

// Get all attachments (non-recursive)
$attachments = $message->getAttachments();
foreach ($attachments as $attachment) {
    echo "Filename: " . $attachment->getHeaderAttribute('content-disposition', 'filename') . "\n";
    echo "Content-Type: " . $attachment->getContentType() . "\n";
    echo "Size: " . strlen($attachment->getContents()) . " bytes\n";
}

// Get all attachments recursively (including nested)
$allAttachments = $message->getAttachments(true);
```

### Working with Nested Messages

```php
$message = $parser->parse('nested-message.eml');

// Check if a part is a nested message
$parts = $message->getParts();
foreach ($parts as $part) {
    if ($part->isMessage()) {
        // This is a message/rfc822 part
        $nestedMessage = $part->getParts()[0];
        echo "Nested Subject: " . $nestedMessage->getHeaderValue('subject') . "\n";
    }
}
```

### Accessing Header Attributes

```php
$message = $parser->parse('email.eml');

// Get a header object
$contentType = $message->getHeader('content-type');

// Get header value
echo $contentType->getValue();  // "text/plain; charset=UTF-8"

// Get all attributes
$attributes = $contentType->getAttributes();
// ['charset' => 'UTF-8', 'format' => 'flowed']

// Get specific attribute
echo $contentType->getAttribute('charset');  // "UTF-8"
```

### Recursive Part Traversal

```php
$message = $parser->parse('complex-nested.eml');

// Get all parts recursively (including nested parts)
$allParts = $message->getParts(true);

// Get all attachments recursively (including from nested messages)
$allAttachments = $message->getAttachments(true);
```

## API Reference

### MessageParser

#### Methods

- `parse(string|array|\Iterator $payload, bool $withSubMessage = true): MessagePartInterface`
    - Parses an email from file path, array of lines, or iterator
    - `$withSubMessage`: Whether to parse nested message/rfc822 parts recursively

### MessagePartInterface

#### Header Methods

- `getHeaders(): array<string, HeaderInterface>` - Get all headers
- `getHeader(string $name): ?HeaderInterface` - Get a specific header (case-insensitive)
- `getHeaderValue(string $name, ?string $default = null): ?string` - Get header value
- `getHeaderAttribute(string $header, string $attr, ?string $default = null): ?string` - Get header attribute

#### Content Type Methods

- `getContentType(): string` - Get Content-Type (defaults to "text/plain")
- `isMultiPart(): bool` - Check if multipart message
- `isMessage(): bool` - Check if message/rfc822 type
- `isText(): bool` - Check if text/\* content type

#### Content Methods

- `getContents(): string` - Get message body content (preamble for multipart)

#### Parts and Attachments

- `getParts(bool $recursive = false): array<MessagePartInterface>` - Get nested parts
- `getAttachments(bool $recursive = false): array<MessagePartInterface>` - Get attachments

### HeaderInterface

- `getValue(): ?string` - Get header value
- `getAttributes(): array<string, string>` - Get all attributes
- `getAttribute(string $name): ?string` - Get specific attribute

## Examples

### Example 1: Simple Text Email

```php
$parser = new MessageParser();
$message = $parser->parse('simple-email.eml');

echo "Subject: " . $message->getHeaderValue('subject') . "\n";
echo "From: " . $message->getHeaderValue('from') . "\n";
echo "Body: " . $message->getContents() . "\n";
```

### Example 2: Multipart with Attachments

```php
$parser = new MessageParser();
$message = $parser->parse('email-with-attachments.eml');

// Get text parts
$parts = $message->getParts();
foreach ($parts as $part) {
    if ($part->isText()) {
        echo "Text part: " . $part->getContents() . "\n";
    }
}

// Get attachments
$attachments = $message->getAttachments();
foreach ($attachments as $attachment) {
    $filename = $attachment->getHeaderAttribute('content-disposition', 'filename');
    file_put_contents($filename, $attachment->getContents());
}
```

### Example 3: Complex Nested Structure

```php
$parser = new MessageParser();
$message = $parser->parse('complex-nested.eml');

// Traverse all parts recursively
function processPart($part, $level = 0) {
    $indent = str_repeat('  ', $level);
    echo $indent . "Type: " . $part->getContentType() . "\n";

    if ($part->isMultiPart()) {
        foreach ($part->getParts() as $subPart) {
            processPart($subPart, $level + 1);
        }
    }
}

processPart($message);
```

## Supported Email Formats

- Plain text emails
- Multipart/mixed
- Multipart/alternative
- Multipart/related
- Multipart/signed
- Message/rfc822 (nested messages)
- Headers with folded lines
- Headers with attributes (charset, boundary, filename, etc.)
- Attachments with Content-Disposition

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Project Home

https://github.com/vaibhavpandeyvpz/phemail
