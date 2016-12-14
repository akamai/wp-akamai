<?php
/**
 * Akamai {OPEN} EdgeGrid Auth for PHP
 *
 * Provides Request Signing as per
 * {@see https://developer.akamai.com/introduction/Client_Auth.html}
 * as GuzzleHttp a Middleware Handlers
 *
 * @author Davey Shafik <dshafik@akamai.com>
 * @copyright Copyright 2016 Akamai Technologies, Inc. All rights reserved.
 * @license Apache 2.0
 * @link https://github.com/akamai-open/edgegrid-auth-php
 * @link https://developer.akamai.com
 * @link https://developer.akamai.com/introduction/Client_Auth.html
 */

namespace Akamai\Open\EdgeGrid\Authentication\Exception;

use Akamai\Open\EdgeGrid\Authentication\Exception;

/**
 * Class SignerException
 * @package Akamai\Open\EdgeGrid\Authentication
 */
class SignerException extends Exception
{
}
