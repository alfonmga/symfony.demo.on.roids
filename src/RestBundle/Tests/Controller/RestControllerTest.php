<?php

namespace RestBundle\Tests\Controller;

use AppBundle\Entity\Comment,
    AppBundle\Entity\Post;

use Symfony\Component\HttpFoundation\Response;

use Bazinga\Bundle\RestExtraBundle\Test\WebTestCase;

/**
 * Class RestControllerTest
 * @package RestBundle\Tests\Controller
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class RestControllerTest extends WebTestCase
{
    protected $entityManager;

    public function __construct()
    {
        self::bootKernel(array('environment' => 'test', 'debug' => 'true'));
        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testResourcesIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/v1/');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Resources Index")')->count()
        );
    }

    public function testApiDocIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/doc');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("API documentation")')->count()
        );
    }

    public function testResponseFormats()
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/.json');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($response);

        $client->request('GET', '/api/v1/.xml');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertTrue(
            $response->headers->contains('content-type', 'text/xml; charset=UTF-8'),
            $response->headers
        );

        $client->request('GET', '/api/v1/');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertTrue(
            $response->headers->contains('content-type', 'text/html; charset=UTF-8'),
            $response->headers
        );
    }

    public function testGetPosts()
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/posts.json');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testGetPost()
    {
        $post = $this->getExamplePostEntity();
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $client = static::createClient();

        $client->request('GET', 'api/v1/posts/' . $post->getId() . '.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $postResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($post->getTitle(), $postResponse['title']);

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function testNewPost()
    {
        $client = static::createClient();

        $client->request(
            'POST', '/api/v1/posts.json', array(), array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            json_encode($this->getExamplePostData())
        );
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $client->request('GET', $client->getResponse()->headers->get('location') . '.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $buildJsonResponse = json_decode($client->getResponse()->getContent(), true);

        $postToRemove = $this->entityManager->getRepository('AppBundle:Post')->find($buildJsonResponse["id"]);
        $this->entityManager->remove($postToRemove);
        $this->entityManager->flush();
    }

    public function testEditPost()
    {
        $post = $this->getExamplePostEntity();
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $editPostData['post']['summary'] = 'This summary has been edited.';

        $client = static::createClient();

        $client->request(
            'PATCH', '/api/v1/posts/' . $post->getId() . '.json', array(), array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            json_encode($editPostData)
        );
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/v1/posts/' . $post->getId() . '.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $newPostData = json_decode($client->getResponse()->getContent(), true);

        $this->assertFalse($post->getSummary() === $newPostData['summary']);

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function testRemovePost()
    {
        $post = $this->getExamplePostEntity();
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $client = static::createClient();

        $client->request('GET', '/api/v1/posts/' . $post->getId() . '.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/api/v1/posts/'. $post->getId() .'.json');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/v1/posts/' . $post->getId() . '.json');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testGetComments()
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/comments.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testGetCommentsFromSpecificPost()
    {
        $post = $this->getExamplePostEntity();
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $client = static::createClient();

        $client->request('GET', '/api/v1/comments/posts/' . $post->getId() . '.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $postComments = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(0, $postComments);

        $comment = $this->getExampleCommentEntity();
        $comment->setPost($post);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $client->request('GET', '/api/v1/comments/posts/' . $post->getId() . '.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $postComments = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $postComments);

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function testGetComment()
    {
        $post = $this->getExamplePostEntity();
        $comment = $this->getExampleCommentEntity();
        $comment->setPost($post);
        $this->entityManager->persist($post);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $client = static::createClient();

        $client->request('GET', '/api/v1/comments/' . $comment->getId() . '.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $commentResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($comment->getContent(), $commentResponse['content']);

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function testNewComment()
    {
        $post = $this->getExamplePostEntity();
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $client = static::createClient();

        $client->request(
            'POST', '/api/v1/comments/posts/' . $post->getId() . '.json', array(), array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            json_encode($this->getExampleCommentData())
        );
        $this->assertEquals($client->getResponse()->getStatusCode(), Response::HTTP_CREATED);

        $client->request('GET', $client->getResponse()->headers->get('location') . '.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function testEditComment()
    {
        $post = $this->getExamplePostEntity();
        $comment = $this->getExampleCommentEntity();
        $comment->setPost($post);
        $this->entityManager->persist($post);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
        
        $editCommentData['comment']['content'] = 'This content has been edited.';

        $client = static::createClient();

        $client->request(
            'PATCH', '/api/v1/comments/' . $comment->getId() . '.json', array(), array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            json_encode($editCommentData)
        );
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/v1/comments/' . $comment->getId() . '.json');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $newCommentData = json_decode($client->getResponse()->getContent(), true);

        $this->assertFalse($comment->getContent() === $newCommentData['content']);

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function testRemoveComment()
    {
        $client = static::createClient();

        $post = $this->getExamplePostEntity();
        $comment = $this->getExampleCommentEntity();
        $comment->setPost($post);
        $this->entityManager->persist($post);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $client->request('DELETE', '/api/v1/comments/' . $comment->getId() . '.json');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/v1/comments/' . $comment->getId() . '.json');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function getExamplePostData()
    {
        $post['post']['title'] = 'Eros diam egestas libero eu vulputate risus';
        $post['post']['summary'] = 'Sed varius a risus eget aliquam Pellentesque et sapien pulvinar consectetur';
        $post['post']['content'] = 'Lorem ipsum dolor sit amet consectetur adipisicing elit, sed do eiusmod tempor
            incididunt ut labore et **dolore magna aliqua**: Duis aute irure dolor in
            reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';
        $post['post']['authorEmail'] = 'anna_admin@symfony.com';

        return $post;
    }

    public function getExampleCommentData()
    {
        $comment['comment']['content'] = 'Lorem ipsum dolor sit amet consectetur.';
        $comment['comment']['authorEmail'] = 'anna_admin@symfony.com';

        return $comment;
    }

    public function getExamplePostEntity()
    {
        $post = new Post();
        $post->setTitle('Eros diam egestas libero eu vulputate risus');
        $post->setSlug('eros-diam-egestas-libero-eu-vulputate-risus');
        $post->setSummary('Sed varius a risus eget aliquam Pellentesque et sapien pulvinar consectetur In
            hac habitasse platea dictumst Urna nisl sollicitudin id varius orci quam id turpis Ut eleifend mauris
            et risus ultrices egestas Aliquam sodales odio id eleifend tristique Ut suscipit posuere justo at
            vulputate');
        $post->setContent('Lorem ipsum dolor sit amet consectetur adipisicing elit, sed do eiusmod tempor
            incididunt ut labore et **dolore magna aliqua**: Duis aute irure dolor in
            reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
            Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
            deserunt mollit anim id est laborum.');
        $post->setAuthorEmail('anna_admin@symfony.com');

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }

    public function getExampleCommentEntity()
    {
        $comment = new Comment();
        $comment->setContent('Lorem ipsum dolor sit amet consectetur.');
        $comment->setAuthorEmail('anna_admin@symfony.com');

        return $comment;
    }
}