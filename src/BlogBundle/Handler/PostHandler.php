<?php

namespace BlogBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use BlogBundle\Model\PostInterface;
use BlogBundle\Model\PostHandlerInterface;
use BlogBundle\Form\Type\PostType;
use BlogBundle\Exception\InvalidFormException;

class PostHandler implements PostHandlerInterface
{
    /**
     * @var Doctrine\Common\Persistence\ObjectManager $om
     */
    private $om;

    /**
     * @var BlogBundle\Entity\Post $entityClass
     */
    private $entityClass;

    /**
     * @var Doctrine\ORM\EntityRepository $repository
     */
    private $repository;

    /**
     * @var Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    private $formFactory;

    /**
     * @param Doctrine\Common\Persistence\ObjectManager   $om
     * @param BlogBundle\Entity\Post                      $entityClass
     * @param Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    public function __construct(ObjectManager $om, $entityClass, FormFactoryInterface $formFactory)
    {
        $this->om           = $om;
        $this->entityClass  = $entityClass;
        $this->repository   = $this->om->getRepository($this->entityClass);
        $this->formFactory  = $formFactory;
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
        return $this->repository->findBy(array(), $orderby, $limit, $offset);
    }

    /**
     * Get a Post.
     *
     * @param mixed $id
     *
     * @return PostInterface
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new Post.
     *
     * @param array $parameters
     *
     * @return PostInterface
     */
    public function post(array $parameters)
    {
        $post = $this->createPost();

        return $this->processForm($post, $parameters, 'POST');
    }

    /**
    * Edit a Post, or create if not exist.
    *
    * @param PostInterface $post
    * @param array         $parameters
    *
    * @return PostInterface
    */
    public function put(PostInterface $post, array $parameters)
    {
        return $this->processForm($post, $parameters, 'PUT');
    }

    /**
    * Partially update a Post.
    *
    * @param PostInterface $post
    * @param array         $parameters
    *
    * @return PostInterface
    */
    public function patch(PostInterface $post, array $parameters)
    {
        return $this->processForm($post, $parameters, 'PATCH');
    }

    /**
    * Delete a Post.
    *
    * @param PostInterface
    */
    public function delete(PostInterface $post)
    {
        $this->om->remove($post);
        $this->om->flush($post);
    }

    /**
     * Processes the form.
     *
     * @param PostInterface $post
     * @param array         $parameters
     * @param String        $method
     *
     * @return PostInterface
     *
     * @throws InvalidFormException
     */
    private function processForm(PostInterface $post, array $parameters, $method = "PUT")
    {
        $form = $this->formFactory->create(new PostType(), $post, array('method' => $method));
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {
            $post = $form->getData();
            $this->om->persist($post);
            $this->om->flush($post);

            return $post;
        }
        throw new InvalidFormException('Invalid submitted data', $form);
    }

    /**
     * Create a new Post.
     *
     * @return PostInterface
     */
    private function createPost()
    {
        return new $this->entityClass();
    }
}
