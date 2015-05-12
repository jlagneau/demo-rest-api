<?php

namespace BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use BlogBundle\Entity\Article;
use BlogBundle\Exception\InvalidFormException;

/**
 * @FOSRest\NamePrefix("api_v1_")
 */
class ArticleController extends FOSRestController
{
    /**
     * List all Articles.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @FOSRest\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing articles.")
     * @FOSRest\QueryParam(name="limit", requirements="\d+", default="5", description="How many articles to return.")
     *
     * @FOSRest\View(templateVar="articles")
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getArticlesAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset') === null ? 0 : $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');

        return $this->container->get('article_handler')->all($limit, $offset);
    }

    /**
     * Get single article,
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets an article for a given id",
     *   output = "BlogBundle\Entity\Article",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the article does not exist"
     *   }
     * )
     *
     * @FOSRest\View(templateVar="article")
     *
     * @param Article $id the Article id
     *
     * @return array
     *
     * @throws NotFoundHttpException when the article does not exist
     */
    public function getArticleAction(Article $id)
    {
        $article = $this->container
                     ->get('article_handler')
                     ->get($id);

        return $this->view($article, 200);
    }

    /**
     * Create an article from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new article from the submitted data.",
     *   input = "BlogBundle\Form\Type\ArticleType",
     *   statusCodes = {
     *     201 = "Returned when the article is created",
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
    public function postArticleAction(Request $request)
    {
        try {
            $newArticle = $this->container->get('article_handler')->post(
                $request->request->all()
            );
            $routeOptions = array(
                'id'        => $newArticle->getId(),
                '_format'   => $request->get('_format'),
            );

            return $this->routeRedirectView('api_v1_get_article', $routeOptions, Codes::HTTP_CREATED);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
    * Update existing article from the submitted data or create a new article at a specific location.
    *
    * @ApiDoc(
    *   resource = true,
    *   input = "BlogBundle\Form\Type\ArticleType",
    *   statusCodes = {
    *     201 = "Returned when the article is created",
    *     303 = "Returned when the article is edited",
    *     400 = "Returned when the form has errors",
    *     404 = "Returned when the article does not exist"
    *   }
    * )
    *
    * @FOSRest\View(templateVar = "form")
    *
    * @param Request $request the request object
    * @param int     $id      the article id
    *
    * @return FormTypeInterface|View
    *
    * @throws NotFoundHttpException when the article does not exist
    */
    public function putArticleAction(Request $request, $id)
    {
        try {
            if (!($article = $this->container->get('article_handler')->get($id))) {
                $statusCode = Codes::HTTP_CREATED;
                $article = $this->container->get('article_handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Codes::HTTP_SEE_OTHER;
                $article = $this->container->get('article_handler')->put(
                    $article,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id'        => $article->getId(),
                '_format'   => $request->get('_format'),
            );

            return $this->routeRedirectView('api_v1_get_article', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
    * Update partially an existing article from the submitted data.
    *
    * @ApiDoc(
    *   resource = true,
    *   input = "BlogBundle\Form\Type\ArticleType",
    *   statusCodes = {
    *     204 = "Returned when the article was successfully patched",
    *     400 = "Returned when the form has errors",
    *     404 = "Returned when the article does not exist"
    *   }
    * )
    *
    * @FOSRest\View(templateVar = "form")
    *
    * @param Request $request the request object
    * @param Article $id      the article id
    *
    * @return FormTypeInterface|View
    *
    * @throws NotFoundHttpException when the article does not exist
    */
    public function patchArticleAction(Request $request, Article $id)
    {
        try {
            $article = $this->container->get('article_handler')->patch(
                $this->container->get('article_handler')->get($id),
                $request->request->all()
            );

            return $this->view($article, Codes::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
    * Delete a single article.
    *
    * @ApiDoc(
    *   resource = true,
    *   description = "Delete an article for a given id.",
    *   statusCodes = {
    *     204 = "Returned when the article was successfully deleted",
    *     404 = "Returned when the article does not exist"
    *   }
    * )
    *
    * @FOSRest\View(templateVar = "data")
    *
    * @param Request $request the request object
    * @param Article $id      the article id
    *
    * @return View
    *
    * @throws NotFoundHttpException when the article does not exist
    */
    public function deleteArticleAction(Article $id)
    {
        $article = $this->container->get('article_handler')->delete(
            $this->container->get('article_handler')->get($id)
        );
        $this->view($article, Codes::HTTP_NO_CONTENT);
    }
}
