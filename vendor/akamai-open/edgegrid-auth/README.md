# akamai-open/edgegrid-auth

[![License](https://img.shields.io/github/license/akamai-open/AkamaiOPEN-edgegrid-php.png)](https://github.com/akamai-open/AkamaiOPEN-edgegrid-php/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/akamai-open/AkamaiOPEN-edgegrid-php.svg?branch=master)](https://travis-ci.org/akamai-open/AkamaiOPEN-edgegrid-php)
[![Code Coverage](https://scrutinizer-ci.com/g/akamai-open/AkamaiOPEN-edgegrid-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/akamai-open/AkamaiOPEN-edgegrid-php/?branch=master)
[![API Docs](https://img.shields.io/badge/api-docs-blue.svg)](http://akamai-open.github.io/AkamaiOPEN-edgegrid-php-client/)

[Akamai {OPEN} EdgeGrid Authentication] for PHP

> **Note:** in version 0.6.0 of the `akamai-open/edgegrid-client` it was moved to it's
> [own repository](https://github.com/akamai-open/AkamaiOPEN-edgegrid-php-client), and the
> `\Akamai\Open\EdgeGrid\Authentication` library was seperated into the `akamai-open/edgegrid-auth` package (this repo).

This library implements the Akamai {OPEN} EdgeGrid Authentication scheme.

For more information visit the [Akamai {OPEN} Developer Community](https://developer.akamai.com).

## Installation

This library requires PHP 5.3.9+, or HHVM 3.5+ to be used with the built-in Guzzle HTTP client.

> PHP 5.3 has been end of life (EOL) since August 14th, 2014, and has **known** security vulnerabilities, therefore we do not recommend using it. However, we understand that many actively supported LTS distributions are still shipping with PHP 5.3.

To install, use [`composer`](http://getcomposer.org):

```sh
$ composer require akamai-open/edgegrid-auth
```

### Alternative Install: Single File (PHAR)

Alternatively, download the PHAR file from the [releases](https://github.com/akamai-open/AkamaiOPEN-edgegrid-php/releases) page.

To use it, you just include it inside your code:

```php
include 'akamai-open-edgegrid-auth.phar';

// Library is ready to use
```

### Alternative Install: Git/Subversion/ZIP Archive

You can clone [this repository](https://github.com/akamai-open/AkamaiOPEN-edgegrid-php.git) using git,
[this repository](https://github.com/akamai-open/AkamaiOPEN-edgegrid-php) using subversion, or download
the latest [ZIP archive](https://github.com/akamai-open/AkamaiOPEN-edgegrid-php/archive/master.zip) or a
[specific release ZIP archive](https://github.com/akamai-open/AkamaiOPEN-edgegrid-php/releases).

#### Using the Composer Autoloader

To use the composer autoloader, you must install the dependencies using:

```sh
$ composer install
```

Then include the autoloader:

```php
require_once 'vendor/autoload.php';
```

#### Without Composer Autoloader

Include all the required classes manually in your code:

```php
require_once 'src/Authentication.php';
require_once 'src/Authentication/Timestamp.php';
require_once 'src/Authentication/Nonce.php';
require_once 'src/Authentication/Exception.php';
require_once 'src/Authentication/Exception/ConfigException.php';
require_once 'src/Authentication/Exception/SignerException.php';
require_once 'src/Authentication/Exception/SignerException/InvalidSignDataException.php';
```

### Usage

Once you have installed the library, you can create the header value by calling the appropriate
[`\Akamai\Open\Edgegrid\Authentication::set*()` methods](https://akamai-open.github.io/AkamaiOPEN-edgegrid-php-client/class-Akamai.Open.EdgeGrid.Authentication.html#methods).
For example, using it with the built-in streams HTTP client might look like the following:

```php
$auth = \Akamai\Open\EdgeGrid\Authentication::createFromEdgeRcFile('default', '/.edgerc');
$auth->setHttpMethod('GET');
$auth->setPath('/diagnostic-tools/v1/locations');

$context = array(
	'http' => array(
		'header' => array(
			'Authorization: ' . $auth->createAuthHeader(),
			'Content-Type: application/json'
		)
	)
);

$context = stream_context_create($context);

$response = json_decode(file_get_contents('https://' . $auth->getHost() . $auth->getPath(), null, $context));
```

## Author

Davey Shafik <dshafik@akamai.com>

## License

Copyright 2016 Akamai Technologies, Inc.  All rights reserved.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at <http://www.apache.org/licenses/LICENSE-2.0>.

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

[Akamai {OPEN} EdgeGrid Authentication]: https://developer.akamai.com/introduction/Client_Auth.html