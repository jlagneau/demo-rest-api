<?php

namespace BlogBundle\Model;

interface PostInterface
{
    /**
    * Set title
    *
    * @param string $title
    * @return PostInterface
    */
    public function setTitle($title);

    /**
    * Get title
    *
    * @return string
    */
    public function getTitle();

    /**
    * Set content
    *
    * @param string $content
    * @return PostInterface
    */
    public function setContent($content);

    /**
    * Get content
    *
    * @return string
    */
    public function getContent();
}
