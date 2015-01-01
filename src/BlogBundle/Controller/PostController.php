<?php

namespace BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use BlogBundle\Entity\Post;
use BlogBundle\Exception\InvalidFormException;

/**
 * @FOSRest\NamePrefix("api_v1_")
 */
class PostController extends FOSRestController
{
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
     * @FOSRest\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing posts.")
     * @FOSRest\QueryParam(name="limit", requirements="\d+", default="5", description="How many posts to return.")
     *
     * @FOSRest\View(templateVar="posts")
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getPostsAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset') === null ? 0 : $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');

        return $this->container->get('post_handler')->all($limit, $offset);
    }

    /**
     * Get single Post,
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets a Post for a given id",
     *   output = "BlogBundle\Entity\Post",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the post is not found"
     *   }
     * )
     *
     * @FOSRest\View(templateVar="post")
     *
     * @param Post $id the post id
     *
     * @return array
     *
     * @throws NotFoundHttpException when post not exist
     */
    public function getPostAction(Post $id)
    {
        $post = $this->container
                     ->get('post_handler')
                     ->get($id);

        return $this->view($post, 200);
    }

    /**
     * Create a Post from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new post from the submitted data.",
     *   input = "BlogBundle\Form\Type\PostType",
     *   statusCodes = {
     *     201 = "Returned when the Post is created",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @FOSRest\View(
     *  statusCode = Codes::HTTP_BAD_REQUEST,
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface|View
     */
    public function postPostAction(Request $request)
    {
        try {
            $newPost = $this->container->get('post_handler')->post(
                $request->request->all()
            );
            $routeOptions = array(
                'id'        => $newPost->getId(),
                '_format'   => $request->get('_format'),
            );

            return $this->routeRedirectView('api_v1_get_post', $routeOptions, Codes::HTTP_CREATED);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
    * Update existing post from the submitted data or create a new post at a specific location.
    *
    * @ApiDoc(
    *   resource = true,
    *   input = "BlogBundle\Form\Type\PostType",
    *   statusCodes = {
    *     201 = "Returned when the Post is created",
    *     303 = "Returned when the Post is edited",
    *     400 = "Returned when the form has errors"
    *   }
    * )
    *
    * @FOSRest\View(templateVar = "form")
    *
    * @param Request $request the request object
    * @param int     $id      the post id
    *
    * @return FormTypeInterface|View
    *
    * @throws NotFoundHttpException when post not exist
    */
    public function putPostAction(Request $request, $id)
    {
        try {
            if (!($post = $this->container->get('post_handler')->get($id))) {
                $statusCode = Codes::HTTP_CREATED;
                $post = $this->container->get('post_handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Codes::HTTP_SEE_OTHER;
                $post = $this->container->get('post_handler')->put(
                    $post,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id'        => $post->getId(),
                '_format'   => $request->get('_format'),
            );

            return $this->routeRedirectView('api_v1_get_post', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
    * Update partially an existing post from the submitted data.
    *
    * @ApiDoc(
    *   resource = true,
    *   input = "BlogBundle\Form\Type\PostType",
    *   statusCodes = {
    *     204 = "Returned when the Post was successfully patched",
    *     400 = "Returned when the form has errors",
    *     404 = "Returned when the Post does not exist"
    *   }
    * )
    *
    * @FOSRest\View(templateVar = "form")
    *
    * @param Request $request the request object
    * @param Post    $id      the post id
    *
    * @return FormTypeInterface|View
    *
    * @throws NotFoundHttpException when post not exist
    */
    public function patchPostAction(Request $request, Post $id)
    {
        try {
            $post = $this->container->get('post_handler')->patch(
                $this->container->get('post_handler')->get($id),
                $request->request->all()
            );

            return $this->view($post, Codes::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
    * Delete a single Post.
    *
    * @ApiDoc(
    *   resource = true,
    *   description = "Delete a Post for a given id.",
    *   statusCodes = {
    *     204 = "Returned when the Post was successfully deleted",
    *     404 = "Returned when the Post does not exist"
    *   }
    * )
    *
    * @FOSRest\View(templateVar = "data")
    *
    * @param Request $request the request object
    * @param Post    $id      the post id
    *
    * @return View
    *
    * @throws NotFoundHttpException when post not exist
    */
    public function deletePostAction(Post $id)
    {
        $post = $this->container->get('post_handler')->delete(
            $this->container->get('post_handler')->get($id)
        );
        $this->view($post, Codes::HTTP_NO_CONTENT);
    }
}
