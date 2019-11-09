<?php


namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserService
{
    protected $em;
    protected $container;

    public function __construct(EntityManagerInterface $entityManager,
                                ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    public function ReturnUsersAndBloggers($request)
    {
        $em = $this->em;
        $container = $this->container;
        $query = $em->createQuery(
            "
                SELECT
                        t.id,
                        t.email,
                        t.roles,
                        t.firstName,
                        t.lastName,
                        t.image
                FROM
                    App\Entity\Users t
                 WHERE
                    t.roles LIKE '[%U%]' OR t.roles LIKE '[%B%]'
            "
        );
        //$result = $query->execute();
        $paginator = $container->get('knp_paginator');
        $result = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 1)
        );
        return ($result);
    }

    public function ReturnAllUsers($request)
    {
        $em = $this->em;
        $container = $this->container;
        $query = $em->createQuery(
            "
                SELECT
                        t.id,
                        t.email,
                        t.roles,
                        t.firstName,
                        t.lastName,
                        t.image
                FROM
                    App\Entity\Users t
            "
        );
        //$result = $query->execute();
        $paginator = $container->get('knp_paginator');
        $result = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 1)
        );
        return ($result);
    }
}