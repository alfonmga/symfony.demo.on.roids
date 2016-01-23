<?php

namespace RabbitMQBundle\Tests\Controller;

use AppBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RabbitMQControllerTest extends WebTestCase
{
    protected $entityManager;

    public function __construct()
    {
        self::bootKernel(array('environment' => 'test', 'debug' => 'true'));
        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testRabbitMQ()
    {
        $client = static::createClient();

        $post = new Post();
        $post->setTitle('Lorem ipsum dolor');
        $post->setSlug('Lorem-ipsum-dolor');
        $post->setSummary('Lorem ipsum dolor sit amet consectetur adipiscing elit Urna nisl sollicitudin');
        $post->setContent('Lorem ipsum dolor sit amet consectetur adipiscing elit Urna nisl sollicitudin');
        $post->setAuthorEmail('anna_admin@symfony.com');

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $client->request('POST', '/post/generate_pdf/' . $post->getId());
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $pdfName = json_decode($client->getResponse()->getContent(), true)['pdfName'];

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        $pdfPath = self::$kernel->getRootDir() . '/../web/downloads/pdf/' . $pdfName . '.pdf';

        sleep(2);
        $this->assertTrue(file_exists($pdfPath));
        unlink($pdfPath);
    }
}
