<?php

namespace BlogBundle\Entity;

use BlogBundle\Model\ArticleInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * Article.
 *
 * @ORM\Entity
 * @ORM\Table(name="blog_article")
 */
class Article implements ArticleInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

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
     *      max = 128,
     *      minMessage = "blog_bundle.article.title.min_message",
     *      maxMessage = "blog_bundle.article.title.max_message"
     * )
     * @ORM\Column(name="title", type="string", length=128)
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
     * @var string
     *
     * @Gedmo\Slug(fields={"createdAt", "title"}, updatable=false, unique=true, dateFormat="d-m-Y")
     * @Doctrine\ORM\Mapping\Column(length=255, unique=true)
     */
    private $slug;

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

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Article
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
