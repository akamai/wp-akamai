<?php
/**
 * Akamai {OPEN} EdgeGrid Auth for PHP
 *
 * @author Davey Shafik <dshafik@akamai.com>
 * @copyright Copyright 2016 Akamai Technologies, Inc. All rights reserved.
 * @license Apache 2.0
 * @link https://github.com/akamai-open/AkamaiOPEN-edgegrid-php
 * @link https://developer.akamai.com
 * @link https://developer.akamai.com/introduction/Client_Auth.html
 */

/**
 * random_bytes stub
 *
 * @see Akamai\Open\EdgeGrid\Tests\Client\Authentication\NonceTest->testMakeNonceRandomBytes()
 *
 * @param $size
 * @return string
 */
if (!function_exists("random_bytes")) {
    function random_bytes($size)
    {
        return __FUNCTION__;
    }
}
