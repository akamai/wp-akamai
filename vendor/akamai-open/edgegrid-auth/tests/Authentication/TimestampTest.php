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

class TimestampTest extends \PHPUnit_Framework_TestCase
{
    public function testTimestampFormat()
    {
        $timestamp = new \Akamai\Open\EdgeGrid\Authentication\Timestamp();

        $check = \DateTime::createFromFormat('Ymd\TH:i:sO', (string) $timestamp, new \DateTimeZone('UTC'));
        $this->assertEquals(
            $check->format(\Akamai\Open\EdgeGrid\Authentication\Timestamp::FORMAT),
            (string) $timestamp
        );
    }


    public function testIsValid()
    {
        $timestamp = new \Akamai\Open\EdgeGrid\Authentication\Timestamp();
        $this->assertTrue($timestamp->isValid());
        $timestamp->setValidFor('PT0S');
        sleep(1);
        $this->assertFalse($timestamp->isValid());
    }
}
