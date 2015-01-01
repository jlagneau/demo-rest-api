<?php

namespace BlogBundle\Tests\Fixtures\Entity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BlogBundle\Entity\Post;
use BlogBundle\Model\PostInterface;

class LoadPostData implements FixtureInterface
{
    /**
     * @var array $post
     */
    public static $posts = array();

    /**
     * Load fixtures.
     */
    public function load(ObjectManager $manager)
    {
        $post1 = $this->createPost();
        $post2 = $this->createPost();
        $post3 = $this->createPost();
        $manager->persist($post1);
        $manager->persist($post2);
        $manager->persist($post3);
        $manager->flush();
        self::$posts[] = $post1;
        self::$posts[] = $post2;
        self::$posts[] = $post3;
    }

    /**
     * Create a new Post entity with content.
     *
     * @return PostInterface
     */
    protected function createPost()
    {
        $post = new Post();
        $post->setTitle('title');
        $post->setContent('content');

        return $post;
    }
}
