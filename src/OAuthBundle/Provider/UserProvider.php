<?php

namespace OAuthBundle\Provider;

use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\OAuthAwareException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserProvider
 * @package OAuthBundle\Provider
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class UserProvider extends OAuthUserProvider
{
    /**
     * @var $entityManager
     */
    protected $entityManager;

    /**
     * @var $container
     */
    protected $container;

    /**
     * UserProvider constructor.
     * @param $entityManager
     * @param $container
     */
    public function __construct(EntityManager $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    /**
     * @param UserResponseInterface $response
     * @return mixed
     * @throws OAuthAwareException
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $tokenLoggedUser = $this->container->get('security.token_storage')->getToken();
        $oauthServiceName = $response->getResourceOwner()->getName();
        $oauthServiceUserId = $response->getUsername();
        $oauthServiceAccessToken = $response->getAccessToken();

        $user = $this->entityManager->getRepository('AppBundle:User')->findOneBy(
            array($oauthServiceName . '_id' => $oauthServiceUserId)
        );

        $setter = 'set'.ucfirst($oauthServiceName);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';

        if (null === $user) {
            if(null === $tokenLoggedUser) {
                throw new AccountNotLinkedException(
                    sprintf('Not linked "%s" account could be found', $oauthServiceName)
                );
            }

            $currentLoggedUser = $tokenLoggedUser->getUser();

            if (in_array('ROLE_ADMIN', $currentLoggedUser->getRoles())) {

                $currentLoggedUser->$setter_id($oauthServiceUserId);
                $currentLoggedUser->$setter_token($oauthServiceAccessToken);

                $this->entityManager->persist($currentLoggedUser);
                $this->entityManager->flush();

                $user = $this->entityManager->getRepository('AppBundle:User')->find($currentLoggedUser->getId());

                return $user;
            } else {
                throw new OAuthAwareException(
                    sprintf('Only users with role "ROLE_ADMIN" can link OAuth accounts.', $oauthServiceName)
                );
            }

        } else {
            $user->$setter_token($response->getAccessToken());
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;
        }
    }
}