<?php

namespace RabbitMQBundle\Services;

use AppBundle\Entity\Post;
use JMS\Serializer\Serializer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PDFGenerator
 * @package RabbitMQBundle\Services
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class PDFGenerator
{
    protected $serializer;

    /**
     * @var \AppBundle\Entity\Post
     */
    protected $post;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * PDFGenerator constructor.
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer, ContainerInterface $container)
    {
        $this->serializer = $serializer;
        $this->container = $container;
    }


    /**
     * @param Post $post
     */
    public function setPost(Post $post)
    {
        $this->post = $post;
    }

    /**
     * @return bool
     */
    public function checkExistingUpdatedPdf()
    {
        if ($this->post->getIsPdfGenerated() === true && $this->post->getGeneratedAt() > $this->post->getUpdatedAt()) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function generateNewPdf()
    {
        $pdfName = $this->generateUniquePdfName();
        $this->post->setPdfName($pdfName);

        $postSerialized = $this->serializer->serialize($this->post, 'json');

        $this->container->get('old_sound_rabbit_mq.generate_pdf_producer')->setContentType('application/json');
        $this->container->get('old_sound_rabbit_mq.generate_pdf_producer')->publish($postSerialized);

        $response = array(
            'pdfName' => $pdfName
        );

        return $response;
    }

    /**
     * @return string
     */
    public function generateUniquePdfName()
    {
       return bin2hex(openssl_random_pseudo_bytes(10));
    }
}