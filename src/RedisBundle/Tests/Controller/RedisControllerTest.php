<?php

namespace RedisBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RedisControllerTest
 * @package RedisBundle\Tests\Controller
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class RedisControllerTest extends WebTestCase
{
    protected $container;

    public function __construct()
    {
        self::bootKernel(array('environment' => 'test', 'debug' => 'true'));
        $this->container = self::$kernel->getContainer();
    }

    public function testRedisController()
    {
        $redis = $this->container->get('snc_redis.default');
        $redis->flushdb();
        $this->assertCount(0, $redis->keys('*'));

        $client = static::createClient();

        $client->request('GET', '/blog/top-5-popular-posts');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(11, $redis->keys('*'));

        $redis->flushdb();
        $this->assertCount(0, $redis->keys('*'));
    }
}