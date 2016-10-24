<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FOSRest\NamePrefix("api_")
 */
class ArticleController extends FOSRestController
{
    /**
     * List all Articles.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusResponse = {
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

        return $this->container->get('article_handler')->all($limit, $offset, ['id' => 'desc']);
    }

    /**
     * Get single article.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get an article for a given id.",
     *   output = "BlogBundle\Entity\Article",
     *   statusResponse = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the article does not exist"
     *   }
     * )
     *
     * @FOSRest\View(templateVar="article")
     *
     * @param Article $id the Article id
     *
     * @throws NotFoundHttpException when the article does not exist
     *
     * @return array
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
     * @Security("has_role('ROLE_API')")
     *
     * @ApiDoc(
     *   authentication=true,
     *   resource = true,
     *   description = "Create a new article from the submitted data.",
     *   input = "BlogBundle\Form\Type\ArticleType",
     *   statusResponse = {
     *     201 = "Returned when the article is created",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when the credentials are missing or insufficient"
     *   }
     * )
     *
     * @FOSRest\View(
     *  statusCode = Response::HTTP_BAD_REQUEST,
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
            $routeOptions = [
                'id' => $newArticle->getId(),
                '_format' => $request->get('_format'),
            ];

            return $this->routeRedirectView('api_get_article', $routeOptions, Response::HTTP_CREATED);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing article from the submitted data or create a new article at a specific location.
     *
     * @Security("has_role('ROLE_API')")
     *
     * @ApiDoc(
     *   authentication=true,
     *   resource = true,
     *   input = "BlogBundle\Form\Type\ArticleType",
     *   statusResponse = {
     *     201 = "Returned when the article is created",
     *     303 = "Returned when the article is edited",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when the credentials are missing or insufficient"
     *   }
     * )
     *
     * @FOSRest\View(templateVar = "form")
     *
     * @param Request $request the request object
     * @param int     $id      the article id
     *
     * @return FormTypeInterface|View
     */
    public function putArticleAction(Request $request, $id)
    {
        $handler = $this->container->get('article_handler');
        try {
            if (!($article = $handler->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $article = $handler->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Response::HTTP_SEE_OTHER;
                $article = $handler->put(
                    $article,
                    $request->request->all()
                );
            }
            $routeOptions = [
                'id' => $article->getId(),
                '_format' => $request->get('_format'),
            ];

            return $this->routeRedirectView('api_get_article', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update partially an existing article from the submitted data.
     *
     * @Security("has_role('ROLE_API')")
     *
     * @ApiDoc(
     *   authentication=true,
     *   resource = true,
     *   input = "BlogBundle\Form\Type\ArticleType",
     *   statusResponse = {
     *     204 = "Returned when the article was successfully patched",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when the credentials are missing or insufficient",
     *     404 = "Returned when the article does not exist"
     *   }
     * )
     *
     * @FOSRest\View(templateVar = "form")
     *
     * @param Request $request the request object
     * @param Article $id      the article id
     *
     * @throws NotFoundHttpException when the article does not exist
     *
     * @return FormTypeInterface|View
     */
    public function patchArticleAction(Request $request, Article $id)
    {
        $handler = $this->container->get('article_handler');
        try {
            $article = $handler->patch(
                $handler->get($id),
                $request->request->all()
            );

            return $this->view($article, Response::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Delete a single article.
     *
     * @Security("has_role('ROLE_API')")
     *
     * @ApiDoc(
     *   authentication=true,
     *   resource = true,
     *   description = "Delete an article for a given id.",
     *   statusResponse = {
     *     204 = "Returned when the article was successfully deleted",
     *     401 = "Returned when the credentials are missing or insufficient",
     *     404 = "Returned when the article does not exist"
     *   }
     * )
     *
     * @FOSRest\View(templateVar = "data")
     *
     * @param Request $request the request object
     * @param Article $id      the article id
     *
     * @throws NotFoundHttpException when the article does not exist
     *
     * @return View
     */
    public function deleteArticleAction(Article $id)
    {
        $handler = $this->container->get('article_handler');
        $article = $handler->delete($handler->get($id));

        return $this->view($article, Response::HTTP_NO_CONTENT);
    }
}
