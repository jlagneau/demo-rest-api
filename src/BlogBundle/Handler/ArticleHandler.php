<?php

namespace BlogBundle\Handler;

use BlogBundle\Exception\InvalidFormException;
use BlogBundle\Form\Type\ArticleType;
use BlogBundle\Model\ArticleHandlerInterface;
use BlogBundle\Model\ArticleInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;

class ArticleHandler implements ArticleHandlerInterface
{
    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    private $om;

    /**
     * @var BlogBundle\Entity\Article
     */
    private $entityClass;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * @var Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param Doctrine\Common\Persistence\ObjectManager   $om
     * @param BlogBundle\Entity\Article                   $entityClass
     * @param Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    public function __construct(ObjectManager $om, $entityClass, FormFactoryInterface $formFactory)
    {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->repository = $this->om->getRepository($this->entityClass);
        $this->formFactory = $formFactory;
    }

    /**
     * Get a list of Pages.
     *
     * @param int $limit  the limit of the result
     * @param int $offset starting from the offset
     *
     * @return array
     */
    public function all($limit = 5, $offset = 0, $orderby = null)
    {
        return $this->repository->findBy([], $orderby, $limit, $offset);
    }

    /**
     * Get a Article.
     *
     * @param mixed $id
     *
     * @return ArticleInterface
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new Article.
     *
     * @param array $parameters
     *
     * @return ArticleInterface
     */
    public function post(array $parameters)
    {
        $article = $this->createArticle();

        return $this->processForm($article, $parameters, 'POST');
    }

    /**
     * Edit a Article, or create if not exist.
     *
     * @param ArticleInterface $article
     * @param array            $parameters
     *
     * @return ArticleInterface
     */
    public function put(ArticleInterface $article, array $parameters)
    {
        return $this->processForm($article, $parameters, 'PUT');
    }

    /**
     * Partially update a Article.
     *
     * @param ArticleInterface $article
     * @param array            $parameters
     *
     * @return ArticleInterface
     */
    public function patch(ArticleInterface $article, array $parameters)
    {
        return $this->processForm($article, $parameters, 'PATCH');
    }

    /**
     * Delete a Article.
     *
     * @param ArticleInterface
     */
    public function delete(ArticleInterface $article)
    {
        $this->om->remove($article);
        $this->om->flush($article);
    }

    /**
     * Processes the form.
     *
     * @param ArticleInterface $article
     * @param array            $parameters
     * @param string           $method
     *
     * @throws InvalidFormException
     *
     * @return ArticleInterface
     */
    private function processForm(ArticleInterface $article, array $parameters, $method = 'PUT')
    {
        $form = $this->formFactory->create(ArticleType::class, $article, ['method' => $method]);
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {
            $article = $form->getData();
            $this->om->persist($article);
            $this->om->flush($article);

            return $article;
        }
        throw new InvalidFormException('Invalid submitted data', $form);
    }

    /**
     * Create a new Article.
     *
     * @return ArticleInterface
     */
    private function createArticle()
    {
        return new $this->entityClass();
    }
}
