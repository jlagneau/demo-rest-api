<?php

namespace BlogBundle\Tests\Fixtures\Entity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BlogBundle\Entity\Article;
use BlogBundle\Model\ArticleInterface;

class LoadArticleData implements FixtureInterface
{
    /**
     * @var array $article
     */
    public static $articles = array();

    /**
     * Load fixtures.
     */
    public function load(ObjectManager $manager)
    {
        $article1 = $this->createArticle();
        $article2 = $this->createArticle();
        $article3 = $this->createArticle();
        $manager->persist($article1);
        $manager->persist($article2);
        $manager->persist($article3);
        $manager->flush();
        self::$articles[] = $article1;
        self::$articles[] = $article2;
        self::$articles[] = $article3;
    }

    /**
     * Create a new Article entity with content.
     *
     * @return ArticleInterface
     */
    protected function createArticle()
    {
        $article = new Article();
        $article->setTitle('title');
        $article->setContent('content');

        return $article;
    }
}
