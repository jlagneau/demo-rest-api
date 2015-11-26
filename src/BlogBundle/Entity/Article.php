<?php

namespace BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BlogBundle\Model\ArticleInterface;

/**
 * Article.
 *
 * @ORM\Entity
 * @ORM\Table(name="blog_article")
 */
class Article implements ArticleInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message = "blog_bundle.article.title.not_blank"
     * )
     * @Assert\Length(
     *      min = 3,
     *      max = 255,
     *      minMessage = "blog_bundle.article.title.min_message",
     *      maxMessage = "blog_bundle.article.title.max_message"
     * )
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message = "blog_bundle.article.content.not_blank"
     * )
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "blog_bundle.article.content.min_message"
     * )
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Article
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Article
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
