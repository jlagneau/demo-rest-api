<?php

namespace BlogBundle\Model;

interface ArticleHandlerInterface
{
    /**
     * Get a Article given the identifier.
     *
     * @api
     *
     * @param mixed $id
     *
     * @return ArticleInterface
     */
    public function get($id);

    /**
     * Create a new Article.
     *
     * @api
     *
     * @param array $parameters
     *
     * @return ArticleInterface
     */
    public function post(array $parameters);

    /**
     * Edit a Article, or create if not exist.
     *
     * @api
     *
     * @param ArticleInterface $article
     * @param array            $parameters
     *
     * @return ArticleInterface
     */
    public function put(ArticleInterface $article, array $parameters);

    /**
     * Partially update a Article.
     *
     * @api
     *
     * @param ArticleInterface $article
     * @param array            $parameters
     *
     * @return ArticleInterface
     */
    public function patch(ArticleInterface $article, array $parameters);

    /**
     * Delete a Article.
     *
     * @api
     *
     * @param ArticleInterface
     */
    public function delete(ArticleInterface $article);
}
