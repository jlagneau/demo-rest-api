<?php
namespace BlogBundle\Model;


interface PostHandlerInterface
{
    /**
    * Get a Post given the identifier
    *
    * @api
    *
    * @param mixed $id
    *
    * @return PostInterface
    */
    public function get($id);

    /**
     * Create a new post
     *
     * @api
     *
     * @param array $parameters
     *
     * @return PostInterface
     */
    public function post(array $parameters);

    /**
     * Edit a Post, or create if not exist.
     *
     * @api
     *
     * @param PostInterface $post
     * @param array         $parameters
     *
     * @return PostInterface
     */
    public function put(PostInterface $post, array $parameters);

    /**
     * Partially update a Post.
     *
     * @api
     *
     * @param PostInterface $post
     * @param array         $parameters
     *
     * @return PostInterface
     */
    public function patch(PostInterface $post, array $parameters);

    /**
     * Delete a Post.
     *
     * @api
     *
     * @param PostInterface
     */
    public function delete(PostInterface $post);
}
