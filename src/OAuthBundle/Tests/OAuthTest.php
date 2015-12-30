<?php

namespace OAuthBundle\Tests;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Class OAuthTest
 * @package OAuthBundle\Tests
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class OAuthTest extends WebTestCase
{
    public function testOAuth()
    {
        $accessToken = array(
            'access_token'  => '986d3212c8aca7122993035d82742270f168db5e'
        );

        $token = new OAuthToken($accessToken, array('ROLE_ADMIN'));
        $this->assertEquals('986d3212c8aca7122993035d82742270f168db5e', $token->getAccessToken());

        $user = new OAuthUser('anna_admin');
        $this->assertEquals('anna_admin', $user->getUsername());
        $token->setUser($user);
        $this->assertSame('ROLE_ADMIN', current($token->getRoles()[0]));
    }
}