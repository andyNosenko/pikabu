<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new Users();

        $user->setEmail('user@mail.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'userpass'
        ));
        $user->setFirstName('UserFirtsName');
        $user->setLastName('UserLastName');
        $user->setIsBlogger(false);
        $manager->persist($user);
        $manager->flush();

        $user2 = new Users();

        $user2->setEmail('admin@mail.com');
        $user2->setRoles(['ROLE_ADMIN']);
        $user2->setPassword($this->passwordEncoder->encodePassword(
            $user2,
            'adminpass'
        ));
        $user2->setFirstName('AdminFirtsName');
        $user2->setLastName('AdminLastName');
        $user2->setIsBlogger(true);
        $manager->persist($user2);
        $manager->flush();

        $user3 = new Users();

        $user3->setEmail('blogger@mail.com');
        $user3->setRoles(['ROLE_BLOGGER']);
        $user3->setPassword($this->passwordEncoder->encodePassword(
            $user3,
            'blogerpass'
        ));
        $user3->setFirstName('BloggerFirtsName');
        $user3->setLastName('BloggerLastName');
        $user3->setIsBlogger(true);
        $manager->persist($user3);
        $manager->flush();

        $user4 = new Users();

        $user4->setEmail('moder@mail.com');
        $user4->setRoles(['ROLE_MODERATOR']);
        $user4->setPassword($this->passwordEncoder->encodePassword(
            $user4,
            'moderpass'
        ));
        $user4->setFirstName('ModerFirtsName');
        $user4->setLastName('ModerLastName');
        $user4->setIsBlogger(true);
        $manager->persist($user4);
        $manager->flush();

    }
}
