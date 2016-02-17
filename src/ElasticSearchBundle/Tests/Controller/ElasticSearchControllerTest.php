<?php

namespace ElasticSearchBundle\Tests\Controller;

use AppBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ElasticSearchControllerTest extends WebTestCase
{
    protected $entityManager;

    public function __construct()
    {
        self::bootKernel(array('environment' => 'test', 'debug' => 'true'));

        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();

        self::populateElasticSearchIndices();
    }

    public function testElasticSearch()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/blog/search-results?q=odio');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals('Results for <b>odio</b> (5)', $crawler->filter('h2#results-info>span')->html());

        $randnumber = rand();

        $post = new Post();
        $post->setTitle('Elasticsearch rocks ' . $randnumber);
        $post->setSlug('elasticsearch-rocks-' . $randnumber);
        $post->setSummary('Lorem ipsum dolor sit amet consectetur adipiscing elit Urna nisl sollicitudin');
        $post->setContent('Lorem ipsum dolor sit amet consectetur adipiscing elit Urna nisl sollicitudin');
        $post->setAuthorEmail('anna_admin@symfony.com');

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $crawler = $client->request('GET', '/blog/search-results?q=Elasticsearch rocks ' . $randnumber);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'Results for <b>Elasticsearch rocks ' . $randnumber . '</b> (1)',
            $crawler->filter('h2#results-info>span')->html()
        );

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function populateElasticSearchIndices()
    {
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application(self::$kernel);
        $application->setAutoExit(false);
        $options = new \Symfony\Component\Console\Input\StringInput('fos:elastica:populate --quiet');
        $application->run($options);
    }
}
