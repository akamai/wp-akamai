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
namespace Akamai\Open\EdgeGrid\Tests\Client\Authentication;

class NonceTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeNonce()
    {
        $nonce = new \Akamai\Open\EdgeGrid\Authentication\Nonce();

        $nonces = array();
        for ($i = 0; $i < 100; $i++) {
            $nonces[] = (string) $nonce;
        }

        $this->assertEquals(100, count(array_unique($nonces)));
    }

    public function testMakeNonceRandomBytes()
    {
        if (!function_exists('random_bytes')) {
            include __DIR__ . '/../random_bytes.php';
        }

        $nonce = new \Akamai\Open\EdgeGrid\Authentication\Nonce();
        $this->assertAttributeEquals('random_bytes', 'function', $nonce);
    }
}
