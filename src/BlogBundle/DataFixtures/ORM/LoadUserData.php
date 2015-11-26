<?php

namespace BlogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Model\UserInterface;
use BlogBundle\Entity\User;

class LoadUserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * Load fixtures.
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $this->createUser();
        $userManager->updateUser($user, true);
        $this->setReference('user', $user);
    }

    /**
     * Create a new User entity with content.
     *
     * @return UserInterface
     */
    protected function createUser()
    {
        $user = new User();
        $user->setUsername('test');
        $user->setEmail('test@example.com');
        $user->setPlainPassword('test');
        $user->setEnabled(true);
        $user->setRoles(array('ROLE_API'));

        return $user;
    }

    /**
     * Set container.
     *
     * @param ContainerInterface
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
