# vaibhavpandeyvpz/phemail
MIME parser written in pure [PHP](http://www.php.net/) for parsing raw emails (.eml) files.

[![Latest Version](https://img.shields.io/github/release/vaibhavpandeyvpz/phemail.svg?style=flat-square)](https://github.com/vaibhavpandeyvpz/phemail/releases) [![Build Status](https://img.shields.io/travis/vaibhavpandeyvpz/phemail/master.svg?style=flat-square)](https://travis-ci.org/vaibhavpandeyvpz/phemail) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/c6f2e35b-2349-452e-b696-17849c5695e0/mini.png)](https://insight.sensiolabs.com/projects/c6f2e35b-2349-452e-b696-17849c5695e0) [![](https://codeclimate.com/github/vaibhavpandeyvpz/phemail/badges/gpa.svg)](https://codeclimate.com/github/vaibhavpandeyvpz/phemail/badges) [![](https://codeclimate.com/github/vaibhavpandeyvpz/phemail/badges/coverage.svg)](https://codeclimate.com/github/vaibhavpandeyvpz/phemail/badges) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/vaibhavpandeyvpz/phemail/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/vaibhavpandeyvpz/phemail/?branch=master) [![Coverage Status](https://coveralls.io/repos/github/vaibhavpandeyvpz/phemail/badge.svg?branch=master)](https://coveralls.io/github/vaibhavpandeyvpz/phemail?branch=master) [![Total Downloads](https://img.shields.io/packagist/dt/vaibhavpandeyvpz/phemail.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/phemail) [![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Install
---
```bash
composer require vaibhavpandeyvpz/phemail
```

Usage
---
Suppose this is your email file named `sample.eml`:

```eml
Mime-Version: 1.0
Message-Id: <6B7EC235-5B17-4CA8-B2B8-39290DEB43A3@vaibhavpandey.com>
From: Vaibhav Pandey <contact@vaibhavpandey.com>
To: Vaibhav Pandey <me@vaibhavpandey.com>
Subject: Testing simple email
Date: Sat, 22 Nov 2008 15:04:59 +1100
Content-Type: text/plain; charset=US-ASCII; format=flowed
Content-Transfer-Encoding: 7bit


This is simple as f*** plain text email message.

Regards,
Vaibhav Pandey
```

You can read & parse it as follows:

```php
<?php

$parser = new Phemail\MessageParser();
$message = $parser->parse(__DIR__ . '/sample.eml');

echo $message->getHeaderValue('subject');
# outputs 'Testing simple email'

echo $message->getHeaderValue('date');
# outputs 'Sat, 22 Nov 2008 15:04:59 +1100'

echo $message->getHeaderValue('content-type');
# outputs 'text/plain'

echo $message->getHeaderAttribute('content-type', 'charset');
# outputs 'US-ASCII'

echo $message->getContents();

/**
 * @desc To extract emails from headers, you could use any RFC 822
 *      internet address parser e.g., pear/mail.
 */
$addresses = (new Mail_RFC822())->parseAddressList($message->getHeaderValue('to'));
foreach ($addresses as $address) {
    echo 'Name: ', $address->personal, '<br>', 'Email: ', $address->mailbox, '@', $address->host;
}
```

License
------
See [LICENSE.md](https://github.com/vaibhavpandeyvpz/phemail/blob/master/LICENSE.md) file.
