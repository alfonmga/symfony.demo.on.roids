<?php

namespace OAuthBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Class OAuthController
 * @package OAuthBundle\Controller
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class OAuthController extends Controller
{
    public function disconnectServiceAction(Request $request, $service, $usernameId, $accessToken)
    {
        if ($request->isMethod('POST') && $this->isCsrfTokenValid('authenticate', $request->request->get('_csrf_token'))) {

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AppBundle:User')->findOneBy(array(
               $service . '_id' => $usernameId,
                $service . '_access_token' => $accessToken
            ));

            if ($user && $user->getId() === $this->getUser()->getId()) {

                $user->setGithubId(null);
                $user->setGithubAccessToken(null);

                $em->persist($user);
                $em->flush();
            }
        }

        return $this->redirectToRoute('admin_index');
    }
}