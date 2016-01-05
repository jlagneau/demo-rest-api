<?php

namespace Tests\BlogBundle\Handler;

use BlogBundle\Entity\Article;
use BlogBundle\Handler\ArticleHandler;

class ArticleHandlerTest extends \PHPUnit_Framework_TestCase
{
    const ARTICLE_CLASS = 'Tests\BlogBundle\Handler\DummyArticle';

    /**
     * @var BlogBundle\Handler\ArticleHandler
     */
    private $articleHandler;

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
     * Set up the environement for tests.
     */
    public function setUp()
    {
        $class = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->om->expects($this->any())
             ->method('getRepository')
             ->with($this->equalTo(static::ARTICLE_CLASS))
             ->will($this->returnValue($this->repository));

        $this->om->expects($this->any())
             ->method('getClassMetadata')
             ->with($this->equalTo(static::ARTICLE_CLASS))
             ->will($this->returnValue($class));

        $class->expects($this->any())
              ->method('getName')
              ->will($this->returnValue(static::ARTICLE_CLASS));
    }

    /**
     * Test all method.
     */
    public function testAll()
    {
        $offset = 1;
        $limit = 2;
        $articles = $this->getArticles(2);

        $this->repository
             ->expects($this->once())
             ->method('findBy')
             ->with([], null, $limit, $offset)
             ->will($this->returnValue($articles));

        $this->ArticleHandler = $this->createArticleHandler($this->om, static::ARTICLE_CLASS,  $this->formFactory);
        $all = $this->ArticleHandler->all($limit, $offset);
        $this->assertEquals($articles, $all);
    }

    /**
     * Test get method.
     */
    public function testGet()
    {
        $id = 1;
        $article = $this->getArticle();

        $this->repository->expects($this->once())
             ->method('find')
             ->with($this->equalTo($id))
             ->will($this->returnValue($article));

        $this->ArticleHandler = $this->createArticleHandler($this->om, static::ARTICLE_CLASS,  $this->formFactory);
        $this->ArticleHandler->get($id);
    }

    /**
     * Test Article method.
     */
    public function testArticle()
    {
        $title = 'title1';
        $content = 'content1';
        $parameters = ['title' => $title, 'content' => $content];
        $article = $this->getArticle();
        $form = $this->getMock('Tests\BlogBundle\FormInterface');

        $article->setTitle($title);
        $article->setContent($content);
        $form->expects($this->once())
             ->method('submit')
             ->with($this->anything());
        $form->expects($this->once())
             ->method('isValid')
             ->will($this->returnValue(true));
        $form->expects($this->once())
             ->method('getData')
             ->will($this->returnValue($article));
        $this->formFactory->expects($this->once())
             ->method('create')
             ->will($this->returnValue($form));

        $this->ArticleHandler = $this->createArticleHandler($this->om, static::ARTICLE_CLASS,  $this->formFactory);
        $articleObject = $this->ArticleHandler->post($parameters);
        $this->assertEquals($articleObject, $article);
    }

    /**
     * Test invalid Article method.
     *
     * @expectedException BlogBundle\Exception\InvalidFormException
     */
    public function testArticleShouldRaiseException()
    {
        $title = 'title1';
        $content = 'content1';
        $parameters = ['title' => $title, 'content' => $content];
        $article = $this->getArticle();
        $form = $this->getMock('Tests\BlogBundle\FormInterface');

        $article->setTitle($title);
        $article->setContent($content);
        $form->expects($this->once())
             ->method('submit')
             ->with($this->anything());
        $form->expects($this->once())
             ->method('isValid')
             ->will($this->returnValue(false));
        $this->formFactory->expects($this->once())
             ->method('create')
             ->will($this->returnValue($form));

        $this->ArticleHandler = $this->createArticleHandler($this->om, static::ARTICLE_CLASS,  $this->formFactory);
        $this->ArticleHandler->post($parameters);
    }

    /**
     * Test put method.
     */
    public function testPut()
    {
        $title = 'title1';
        $content = 'content1';
        $parameters = ['title' => $title, 'content' => $content];
        $article = $this->getArticle();
        $form = $this->getMock('Tests\BlogBundle\FormInterface');

        $article->setTitle($title);
        $article->setContent($content);
        $form->expects($this->once())
             ->method('submit')
             ->with($this->anything());
        $form->expects($this->once())
             ->method('isValid')
             ->will($this->returnValue(true));
        $form->expects($this->once())
             ->method('getData')
             ->will($this->returnValue($article));
        $this->formFactory->expects($this->once())
             ->method('create')
             ->will($this->returnValue($form));

        $this->ArticleHandler = $this->createArticleHandler($this->om, static::ARTICLE_CLASS,  $this->formFactory);
        $articleObject = $this->ArticleHandler->put($article, $parameters);
        $this->assertEquals($articleObject, $article);
    }

    /**
     * Test patch method.
     */
    public function testPatch()
    {
        $title = 'title1';
        $content = 'content1';
        $parameters = ['content' => $content];
        $article = $this->getArticle();
        $form = $this->getMock('Tests\BlogBundle\FormInterface');

        $article->setTitle($title);
        $article->setContent($content);
        $form->expects($this->once())
             ->method('submit')
             ->with($this->anything());
        $form->expects($this->once())
             ->method('isValid')
             ->will($this->returnValue(true));
        $form->expects($this->once())
             ->method('getData')
             ->will($this->returnValue($article));
        $this->formFactory->expects($this->once())
             ->method('create')
             ->will($this->returnValue($form));

        $this->ArticleHandler = $this->createArticleHandler($this->om, static::ARTICLE_CLASS,  $this->formFactory);
        $articleObject = $this->ArticleHandler->patch($article, $parameters);
        $this->assertEquals($articleObject, $article);
    }

    /**
     * Get the ArticleHandler.
     *
     * @param ObjectManager    $objectManager
     * @param ArticleInterface $articleClass
     * @param FormFactory      $formFactory
     *
     * @return ArticleHandlerInterface
     */
    protected function createArticleHandler($objectManager, $articleClass, $formFactory)
    {
        return new ArticleHandler($objectManager, $articleClass, $formFactory);
    }

    /**
     * Get a new Article entity.
     *
     * @return ArticleInterface
     */
    protected function getArticle()
    {
        $articleClass = static::ARTICLE_CLASS;

        return new $articleClass();
    }

    /**
     * Get a list of Articles.
     *
     * @param int $maxArticles The number of Articles to retrieve.
     *
     * @return array
     */
    protected function getArticles($maxArticles = 5)
    {
        $articles = [];

        for ($i = 0; $i < $maxArticles; ++$i) {
            $articles[] = $this->getArticle();
        }

        return $articles;
    }
}

class DummyArticle extends Article
{
}
