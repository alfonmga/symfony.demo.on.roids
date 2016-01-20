<?php

namespace RabbitMQBundle\Services\RabbitMQ;

use JMS\Serializer\Serializer;
use PhpAmqpLib\Message\AMQPMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PDFGeneratorConsumer
 * @package RabbitMQBundle\Services\RabbitMQ
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class PDFGeneratorConsumer implements ConsumerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var TwigEngine
     */
    protected $templating;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * PDFGeneratorConsumer constructor.
     * @param ContainerInterface $container
     * @param TwigEngine $templating
     * @param Serializer $serializer
     */
    public function __construct(ContainerInterface $container, TwigEngine $templating, Serializer $serializer)
    {
        $this->container = $container;
        $this->templating = $templating;
        $this->serializer = $serializer;
    }


    /**
     * @param AMQPMessage $msg
     * @return bool
     */
    public function execute(AMQPMessage $msg)
    {
        $post = $this->serializer->deserialize($msg->body, 'AppBundle\Entity\Post', 'json');

        $targetPath = $this->container->get('kernel')->getRootDir() . '/../web/downloads/pdf/' . $post->getPdfName() . '.pdf';

        $this->container->get('knp_snappy.pdf')->generateFromHtml(
            $this->templating->render(
                'RabbitMQBundle::pdf_post_view.html.twig',
                array(
                    'post' => $post
                )
            ),
            $targetPath
        );

        if (file_exists($targetPath)) {
            return true;
        } else {
            return false;
        }
    }
}