<?php

namespace BlogBundle\Tests\Fixtures\Entity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BlogBundle\Entity\Article;
use BlogBundle\Model\ArticleInterface;

class LoadArticleData implements FixtureInterface
{
    /**
     * @var array
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
        $article4 = $this->createArticle();
        $manager->persist($article1);
        $manager->persist($article2);
        $manager->persist($article3);
        $manager->persist($article4);
        $manager->flush();
        self::$articles[] = $article1;
        self::$articles[] = $article2;
        self::$articles[] = $article3;
        self::$articles[] = $article4;
    }

    /**
     * Create a new Article entity with content.
     *
     * @return ArticleInterface
     */
    protected function createArticle()
    {
        static $i = 1;
        $article = new Article();
        $article->setTitle('title '.$i);
        $article->setContent('content '.$i);
        ++$i;

        return $article;
    }
}
