<?php

namespace OAuthBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OAuthControllerTest
 * @package OAuthBundle\Tests\Controller
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class OAuthControllerTest extends WebTestCase
{
    protected $entityManager;

    public function __construct()
    {
        self::bootKernel(array('environment' => 'test', 'debug' => 'true'));
        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testDisconnectService()
    {
        $user = $this->entityManager->getRepository('AppBundle:User')->findOneBy(array(
            'username' => 'anna_admin'
        ));
        $user->setGithubId('123456789');
        $user->setGithubAccessToken('686x3212xacx7121993035d82782270f138db52');

        $this->entityManager->flush();

        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'anna_admin',
            'PHP_AUTH_PW'   => 'kitten',
        ));

        $crawler = $client->request('GET', '/en/admin/post/');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('div.section>form[action^="/oauth/service-disconnect/github/"]')->count());

        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->extract(array('value'));
        $client->request(
            'POST', '/oauth/service-disconnect/github/' . $user->getGithubId() . '/' . $user->getGithubAccessToken(),
            array('_csrf_token' => $csrfToken[0])
        );
        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/en/admin/post/');
        $this->assertGreaterThan(0, $crawler->filter('div.section>a[href="/oauth/connect/github"]')->count());
    }
}