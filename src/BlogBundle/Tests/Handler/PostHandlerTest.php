<?php

namespace BlogBundle\Tests\Handler;

use BlogBundle\Entity\Post;
use BlogBundle\Handler\PostHandler;

class PostHandlerTest extends \PHPUnit_Framework_TestCase
{
    const POST_CLASS = 'BlogBundle\Tests\Handler\DummyPost';

    /**
     * @var BlogBundle\Handler\PostHandler $postHandler
     */
    private $postHandler;

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
     * Set up the environement for tests.
     */
    public function setUp()
    {
        $class              = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->om           = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository   = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->formFactory  = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->om->expects($this->any())
             ->method('getRepository')
             ->with($this->equalTo(static::POST_CLASS))
             ->will($this->returnValue($this->repository));

        $this->om->expects($this->any())
             ->method('getClassMetadata')
             ->with($this->equalTo(static::POST_CLASS))
             ->will($this->returnValue($class));

        $class->expects($this->any())
              ->method('getName')
              ->will($this->returnValue(static::POST_CLASS));
    }

    /**
     * Test all method.
     */
    public function testAll()
    {
        $offset = 1;
        $limit  = 2;
        $posts  = $this->getPosts(2);

        $this->repository
             ->expects($this->once())
             ->method('findBy')
             ->with(array(), null, $limit, $offset)
             ->will($this->returnValue($posts));

        $this->postHandler = $this->createPostHandler($this->om, static::POST_CLASS,  $this->formFactory);
        $all = $this->postHandler->all($limit, $offset);
        $this->assertEquals($posts, $all);
    }

    /**
     * Test get method.
     */
    public function testGet()
    {
        $id     = 1;
        $post   = $this->getPost();

        $this->repository->expects($this->once())
             ->method('find')
             ->with($this->equalTo($id))
             ->will($this->returnValue($post));

        $this->postHandler = $this->createPostHandler($this->om, static::POST_CLASS,  $this->formFactory);
        $this->postHandler->get($id);
    }

    /**
     * Test post method.
     */
    public function testPost()
    {
        $title      = 'title1';
        $content    = 'content1';
        $parameters = array('title' => $title, 'content' => $content);
        $post       = $this->getPost();
        $form       = $this->getMock('BlogBundle\Tests\FormInterface');

        $post->setTitle($title);
        $post->setContent($content);
        $form->expects($this->once())
             ->method('submit')
             ->with($this->anything());
        $form->expects($this->once())
             ->method('isValid')
             ->will($this->returnValue(true));
        $form->expects($this->once())
             ->method('getData')
             ->will($this->returnValue($post));
        $this->formFactory->expects($this->once())
             ->method('create')
             ->will($this->returnValue($form));

        $this->postHandler = $this->createPostHandler($this->om, static::POST_CLASS,  $this->formFactory);
        $postObject = $this->postHandler->post($parameters);
        $this->assertEquals($postObject, $post);
    }

    /**
    * Test invalid post method.
    *
    * @expectedException BlogBundle\Exception\InvalidFormException
    */
    public function testPostShouldRaiseException()
    {
        $title      = 'title1';
        $content    = 'content1';
        $parameters = array('title' => $title, 'content' => $content);
        $post       = $this->getPost();
        $form       = $this->getMock('BlogBundle\Tests\FormInterface');

        $post->setTitle($title);
        $post->setContent($content);
        $form->expects($this->once())
             ->method('submit')
             ->with($this->anything());
        $form->expects($this->once())
             ->method('isValid')
             ->will($this->returnValue(false));
        $this->formFactory->expects($this->once())
             ->method('create')
             ->will($this->returnValue($form));

        $this->postHandler = $this->createPostHandler($this->om, static::POST_CLASS,  $this->formFactory);
        $this->postHandler->post($parameters);
    }

    /**
     * Test put method.
     */
    public function testPut()
    {
        $title      = 'title1';
        $content    = 'content1';
        $parameters = array('title' => $title, 'content' => $content);
        $post       = $this->getPost();
        $form       = $this->getMock('BlogBundle\Tests\FormInterface');

        $post->setTitle($title);
        $post->setContent($content);
        $form->expects($this->once())
             ->method('submit')
             ->with($this->anything());
        $form->expects($this->once())
             ->method('isValid')
             ->will($this->returnValue(true));
        $form->expects($this->once())
             ->method('getData')
             ->will($this->returnValue($post));
        $this->formFactory->expects($this->once())
             ->method('create')
             ->will($this->returnValue($form));

        $this->postHandler = $this->createPostHandler($this->om, static::POST_CLASS,  $this->formFactory);
        $postObject = $this->postHandler->put($post, $parameters);
        $this->assertEquals($postObject, $post);
    }

    /**
     * Test patch method.
     */
    public function testPatch()
    {
        $title      = 'title1';
        $content    = 'content1';
        $parameters = array('content' => $content);
        $post       = $this->getPost();
        $form       = $this->getMock('BlogBundle\Tests\FormInterface');

        $post->setTitle($title);
        $post->setContent($content);
        $form->expects($this->once())
             ->method('submit')
             ->with($this->anything());
        $form->expects($this->once())
             ->method('isValid')
             ->will($this->returnValue(true));
        $form->expects($this->once())
             ->method('getData')
             ->will($this->returnValue($post));
        $this->formFactory->expects($this->once())
             ->method('create')
             ->will($this->returnValue($form));

        $this->postHandler = $this->createPostHandler($this->om, static::POST_CLASS,  $this->formFactory);
        $postObject = $this->postHandler->patch($post, $parameters);
        $this->assertEquals($postObject, $post);
    }

    /**
     * Get the PostHandler.
     *
     * @param ObjectManager $objectManager
     * @param PostInterface $postClass
     * @param FormFactory   $formFactory
     *
     * @return PostHandlerInterface
     */
    protected function createPostHandler($objectManager, $postClass, $formFactory)
    {
        return new PostHandler($objectManager, $postClass, $formFactory);
    }

    /**
     * Get a new Post entity.
     *
     * @return PostInterface
     */
    protected function getPost()
    {
        $postClass = static::POST_CLASS;

        return new $postClass();
    }

    /**
     * Get a list of Posts.
     *
     * @param int $maxPosts The number of Posts to retrieve.
     *
     * @return array
     */
    protected function getPosts($maxPosts = 5)
    {
        $posts = array();

        for ($i = 0; $i < $maxPosts; $i++) {
            $posts[] = $this->getPost();
        }

        return $posts;
    }
}

class DummyPost extends Post
{
}
