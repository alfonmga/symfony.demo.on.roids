<?php

namespace RedisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Snc\RedisBundle\Doctrine\Cache\RedisCache;
use Predis\Client;

/**
 * Class RedisController
 * @package RedisBundle\Controller
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class RedisController extends Controller
{
    public function ListTopFivePopularPostAction()
    {
        $em = $this->getDoctrine()->getManager();

        $predis = new RedisCache();
        $predis->setRedis(new Client());
        $cache_lifetime = 3600;

        $posts = $em->getRepository('AppBundle:Post')
            ->createQueryBuilder('p')
            ->select('p')
            ->getQuery()
            ->setResultCacheDriver($predis)
            ->setResultCacheLifetime($cache_lifetime)
            ->getResult();

        $postsWithNumComments = array();
        foreach ($posts as $post) {
            $postComents = $em->getRepository('AppBundle:Comment')
                ->createQueryBuilder('c')
                ->select('c')
                ->where('c.post = :post_id')
                ->setParameter('post_id', $post->getId())
                ->getQuery()
                ->setResultCacheDriver($predis)
                ->setResultCacheLifetime($cache_lifetime)
                ->getResult();

            array_push(
                $postsWithNumComments, array(
                    'post' => $post,
                    'numComments' => count($postComents)
                )
            );
        }

        usort($postsWithNumComments, function($a, $b) {
            return $b['numComments'] - $a['numComments'];
        });

        $numPosts = count($postsWithNumComments);
        if ($numPosts > 5) {
            for ($i = 5; $i < $numPosts; $i++) {
                unset($postsWithNumComments[$i]);
            }
        }

        return $this->render('RedisBundle:blog:top_posts.html.twig', array(
            'posts' => $postsWithNumComments
        ));
    }
}
