<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostRepository")
 *
 * Defines the properties of the Post entity to represent the blog posts.
 * See http://symfony.com/doc/current/book/doctrine.html#creating-an-entity-class
 *
 * Tip: if you have an existing database, you can generate these entity class automatically.
 * See http://symfony.com/doc/current/cookbook/doctrine/reverse_engineering.html
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class Post
{
    /**
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See http://symfony.com/doc/current/best_practices/configuration.html#constants-vs-configuration-options
     */
    const NUM_ITEMS = 10;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     */
    private $slug;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="post.blank_summary")
     */
    private $summary;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="post.blank_content")
     * @Assert\Length(min = "10", minMessage = "post.too_short_content")
     */
    private $content;

    /**
     * @ORM\Column(type="string")
     * @Assert\Email()
     */
    private $authorEmail;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $generatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $pdfName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPdfGenerated;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Comment",
     *      mappedBy="post",
     *      orphanRemoval=true
     * )
     * @ORM\OrderBy({"publishedAt" = "DESC"})
     */
    private $comments;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->isPdfGenerated = 0;
        $this->comments = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail = $authorEmail;
    }

    /**
     * Is the given User the author of this Post?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user)
    {
        return $user->getEmail() == $this->getAuthorEmail();
    }

    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addComment(Comment $comment)
    {
        $this->comments->add($comment);
        $comment->setPost($this);
    }

    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
        $comment->setPost(null);
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }


    public function getGeneratedAt()
    {
        return $this->generatedAt;
    }

    public function setGeneratedAt($generatedAt)
    {
        $this->generatedAt = $generatedAt;
    }


    public function getPdfName()
    {
        return $this->pdfName;
    }

    public function setPdfName($pdfName)
    {
        $this->pdfName = $pdfName;
    }

    public function getIsPdfGenerated()
    {
        return $this->isPdfGenerated;
    }

    public function setIsPdfGenerated($isPdfGenerated)
    {
        $this->isPdfGenerated = $isPdfGenerated;
    }
}
