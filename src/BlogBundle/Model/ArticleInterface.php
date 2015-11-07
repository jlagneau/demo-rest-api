<?php

namespace BlogBundle\Model;

interface ArticleInterface
{
    /**
     * Set title.
     *
     * @param string $title
     *
     * @return ArticleInterface
     */
    public function setTitle($title);

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return ArticleInterface
     */
    public function setContent($content);

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent();
}
