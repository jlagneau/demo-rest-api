<?php

namespace BlogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BlogBundle\Entity\Article;
use BlogBundle\Model\ArticleInterface;

class LoadArticleData extends AbstractFixture implements FixtureInterface
{
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
        $this->setReference('article-test', $article1);
    }

    /**
     * Create a new Article entity with content.
     *
     * @return ArticleInterface
     */
    protected function createArticle()
    {
        $article = new Article();
        $article->setTitle('Fixture title');
        $article->setContent('Fixture content');

        return $article;
    }
}
