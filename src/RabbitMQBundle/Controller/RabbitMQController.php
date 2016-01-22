<?php

namespace RabbitMQBundle\Controller;

use AppBundle\Entity\Post;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RabbitMQController extends Controller
{
    public function generatePdfAction(Post $post)
    {
        $pdfGenerator = $this->get('rabbitmq_pdf_generator');
        $pdfGenerator->setPost($post);

        if ($pdfGenerator->checkExistingUpdatedPdf()) {
            $response = array(
                'pdfName' => $post->getPdfName()
            );
            return new JsonResponse($response);
        }

        $responsePdfGenerator = $pdfGenerator->generateNewPdf();

        $post->setIsPdfGenerated(true);
        $post->setGeneratedAt(new \DateTime('now'));
        $post->setPdfName($responsePdfGenerator['pdfName']);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new JsonResponse($responsePdfGenerator);
    }
}