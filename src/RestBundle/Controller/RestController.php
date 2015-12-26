<?php

namespace RestBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Post as PostEntity;

use RestBundle\Form\PostRestType,
    RestBundle\Form\CommentRestType;

use FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\FOSRestController,
    FOS\RestBundle\Controller\Annotations\Get,
    FOS\RestBundle\Controller\Annotations\Post,
    FOS\RestBundle\Controller\Annotations\Put,
    FOS\RestBundle\Controller\Annotations\Patch,
    FOS\RestBundle\Controller\Annotations\Delete,
    FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RestController
 * @package RestBundle\Controller
 *
 * @author Alfonso M. García Astorga <me@alfon.io>
 */
class RestController extends FOSRestController
{
    /**
     * List all resources available.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Get("/")
     *
     * @return View
     */
    public function indexApiAction()
    {
        $apiResources = [
            array('Posts' => $this->generateUrl('api_v1_get_posts')),
            array('Comments' => $this->generateUrl('api_v1_get_comments'))
        ];

        $view = $this->view($apiResources)->setTemplate('RestBundle::api_index.html.twig');

        return $this->handleView($view);
    }

    /**
     * List all posts.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Get("/posts")
     *
     * @return View
     */
    public function getPostsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle:Post')->findAll();

        $view = $this->view($posts)->setTemplate('RestBundle:Post:getPosts.html.twig')->setTemplateVar('posts');

        return $this->handleView($view);
    }


    /**
     * Get a specific post.
     *
     * @ApiDoc(
     *   output = "AppBundle\Entity\Post",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the post is not found"
     *   }
     * )
     *
     * @Get("/posts/{id}", requirements={"id" = "\d+"})
     *
     * @param int $id
     * @return View
     */
    public function getPostAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:Post')->find($id);

        if (null === $post) {
            throw $this->createNotFoundException("Post does not exist.");
        }

        $view = $this->view($post)->setTemplate('RestBundle:Post:getPost.html.twig')->setTemplateVar('post');

        return $this->handleView($view);
    }


    /**
     * Presents the form to use to create a new post.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Get("/posts/new", requirements={"_format" = "html"}, name="_hateoas")
     *
     * @return \Symfony\Component\Form\Form
     */
    public function newPostFormAction()
    {
        $form = $this->createForm(new PostRestType());

        $view = $this->view($form)->setTemplate('RestBundle:Post:newPost.html.twig');

        return $this->handleView($view);
    }

    /**
     * Creates a new post from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "AppBundle\Entity\Post",
     *   statusCodes = {
     *     201 = "Returned when a new resource is created",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Post("/posts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function newPostAction(Request $request)
    {
        $post = new PostEntity();
        $form = $this->createForm(new PostRestType(), $post);

        $form->submit($request);
        if ($form->isValid()) {
            $post->setPublishedAt(new \DateTime('now'));
            $post->setSlug($this->get('slugger')->slugify($post->getTitle()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->routeRedirectView('api_v1_get_post', array('id' => $post->getId()));
        }

        $view = $this->view($form)->setTemplate('RestBundle:Post:newPost.html.twig');

        return $this->handleView($view);
    }

    /**
     * Update existing post from the submitted data or create a new post at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "AppBundle\Entity\Post",
     *   statusCodes = {
     *     201 = "Returned when a new resource is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Put("/posts/{id}", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return View
     */
    public function updatePostAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:Post')->find($id);

        if (null === $post) {
            $post = new PostEntity();
            $post->setId($id);
            $em->persist($post);

            $metadata = $em->getClassMetadata(get_class($post));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

            $statusCode = Response::HTTP_CREATED;
        } else {
            $statusCode = Response::HTTP_NO_CONTENT;
        }

        $form = $this->createForm(new PostRestType(), $post);
        $form->submit($request);

        if ($form->isValid()) {
            $post->setSlug($this->get('slugger')->slugify($post->getTitle()));
            $em->flush();

            return $this->routeRedirectView('get_post', array('id' => $post->getId()), $statusCode);
        }

        $view = new View($form);

        return $this->handleView($view);
    }

    /**
     * Presents the form for partial update of an existing Post.
     *
     * @Get("/posts/{id}/edit", requirements={"id" = "\d+", "_format" = "html"}, name="_hateoas")
     *
     * @param $id
     *
     * @return View
     */
    public function editPostFormAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:Post')->find($id);

        $form = $this->createForm(new PostRestType(), $post);

        $view = $this->view($form)->setTemplate('RestBundle:Post:editPost.html.twig');

        return $this->handleView($view);
    }

    /**
     * Partial update of an existing Post.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "AppBundle\Entity\Post",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Patch("/posts/{id}", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return View
     */
    public function editPostAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:Post')->find($id);

        $form = $this->createForm(new PostRestType(), $post);
        $form->submit($request, false);

        if ($form->isValid()) {
            $em->flush();

            return $this->routeRedirectView('api_v1_get_post', array('id' => $post->getId()), Response::HTTP_NO_CONTENT);
        }

        $view = $this->view($form)->setTemplate('RestBundle:Post:editPost.html.twig')->setStatusCode(Response::HTTP_NO_CONTENT);

        return $this->handleView($view);
    }

    /**
     * Removes a post.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "Returned when successful"
     *   }
     * )
     *
     * @Delete("/posts/{id}", requirements={"id" = "\d+"})
     *
     * @param int $id
     * @return View
     */
    public function deletePostAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:Post')->find($id);
        $em->remove($post);
        $em->flush();

        return $this->routeRedirectView('api_v1_get_posts', array(), Response::HTTP_NO_CONTENT);
    }

    /**
     * List all comments.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Get("/comments")
     *
     * @return View
     */
    public function getCommentsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository('AppBundle:Comment')->findAll();

        $view = $this->view($comments)->setTemplate('RestBundle:Comment:getComments.html.twig')->setTemplateVar('comments');

        return $this->handleView($view);
    }

    /**
     * Get a specific comment.
     *
     * @ApiDoc(
     *   output = "AppBundle\Entity\Comment",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the comment is not found"
     *   }
     * )
     *
     * @Get("/comments/{id}", requirements={"id" = "\d+"})
     *
     * @param int $id
     * @return View
     */
    public function getCommentAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:Comment')->find($id);

        if (null === $comment) {
            throw $this->createNotFoundException("Comment does not exist.");
        }

        $view = $this->view($comment)->setTemplate('RestBundle:Comment:getComment.html.twig')->setTemplateVar('comment');

        return $this->handleView($view);
    }

    /**
     * List all comments from a post.
     *
     * @ApiDoc(
     *   output = "AppBundle\Entity\Comment",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   }
     * )
     *
     * @Get("/comments/posts/{id}", requirements={"id" = "\d+"})
     *
     * @param int $id
     * @return View
     */
    public function getCommentsFromPostAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $postComments = $em->getRepository('AppBundle:Post')->find($id);

        $view = new View($postComments->getComments());

        return $this->handleView($view);
    }

    /**
     * Presents the form to use to create a new comment.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Get("/comments/posts/{id}/new", requirements={"_format" = "html"}, name="_hateoas")
     *
     * @return \Symfony\Component\Form\Form
     */
    public function newCommentFormAction()
    {
        $form = $this->createForm(new CommentRestType());

        $view = $this->view($form)->setTemplate('RestBundle:Comment:newComment.html.twig');

        return $this->handleView($view);
    }

    /**
     * Creates a new comment from the submitted data.
     *
     * @Post("/comments/posts/{id}")
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "AppBundle\Entity\Comment",
     *   statusCodes = {
     *     201 = "Returned when a new resource is created",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param Request $request
     * @param int $id
     *
     *
     * @return View
     */
    public function newCommentAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:Post')->find($id);

        $comment = new Comment();
        $comment->setPost($post);

        $form = $this->createForm(new CommentRestType(), $comment);
        $form->submit($request);

        if($form->isValid()) {
            $em->persist($comment);
            $em->flush();

            return $this->routeRedirectView('api_v1_get_comment', array('id' => $comment->getId()));
        }

        $view = $this->view($form)->setTemplate('RestBundle:Comment:newComment.html.twig');

        return $this->handleView($view);
    }

    /**
     * Update existing comment from the submitted data or create a new comment at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "AppBundle\Entity\Comment",
     *   statusCodes = {
     *     201 = "Returned when a new resource is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Put("/comments/{id}", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return View|Response
     */
    public function updateCommentAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:Comment')->find($id);

        if (null === $comment) {
            $comment = new Comment();
            $comment->setId($id);
            $em->persist($comment);

            $metadata = $em->getClassMetadata(get_class($comment));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

            $statusCode = Response::HTTP_CREATED;
        } else {
            $statusCode = Response::HTTP_NO_CONTENT;
        }

        $form = $this->createForm(new CommentRestType(), $comment);
        $form->submit($request);

        if ($form->isValid()) {
            $em->flush();

            return $this->routeRedirectView('get_comment', array('id' => $comment->getId()), $statusCode);
        }

        $view = new View($form);

        return $this->handleView($view);

    }

    /**
     * Presents the form for partial update of an existing Comment.
     *
     * @Get("/comments/{id}/edit", requirements={"id" = "\d+", "_format" = "html"}, name="_hateoas")
     *
     * @param $id
     *
     * @return View
     */
    public function editCommentFormAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:Comment')->find($id);

        $form = $this->createForm(new CommentRestType(), $comment);

        $view = $this->view($form)->setTemplate('RestBundle:Comment:editComment.html.twig');

        return $this->handleView($view);
    }

    /**
     * Partial update of an existing Comment.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "AppBundle\Entity\Comment",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Patch("/comments/{id}", requirements={"id" = "\d+"})
     *
     * @param Request $id
     * @param int $id
     *
     * @return View|Response
     */
    public function editCommentAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:Comment')->find($id);

        $form = $this->createForm(new CommentRestType(), $comment);
        $form->submit($request, false);

        if($form->isValid()) {
            $em->flush();

            return $this->routeRedirectView('api_v1_get_comment', array('id' => $comment->getId()), Response::HTTP_NO_CONTENT);
        }

        $view = $this->view($form)->setTemplate('RestBundle:Comment:editComment.html.twig')->setStatusCode(Response::HTTP_NO_CONTENT);

        return $this->handleView($view);
    }

    /**
     * Removes a comment.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "Returned when successful"
     *   }
     * )
     *
     * @Delete("/comments/{id}")
     *
     * @param int $id
     *
     * @return View
     */
    public function deleteCommentAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:Comment')->find($id);
        $em->remove($comment);
        $em->flush();

        return $this->routeRedirectView('api_v1_get_comments', array(), Response::HTTP_NO_CONTENT);
    }
}
