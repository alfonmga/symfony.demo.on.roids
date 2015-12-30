<?php

namespace OAuthBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * Class AuthenticationSuccessHandler
 * @package OAuthBundle\Services
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var AuthorizationChecker
     */
    protected $security;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * AuthenticationSuccessHandler constructor.
     * @param AuthorizationChecker $security
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     */
    public function __construct(AuthorizationChecker $security, \Symfony\Bundle\FrameworkBundle\Routing\Router $router)
    {
        $this->router = $router;
        $this->security = $security;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($this->security->isGranted('ROLE_ADMIN'))
        {
            return new RedirectResponse($this->router->generate('admin_index'));
        }
        else
        {
            return new RedirectResponse($this->router->generate('blog_index'));
        }
    }
}
