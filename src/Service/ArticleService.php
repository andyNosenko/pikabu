<?php

declare(strict_types = 1);
namespace App\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class ArticleService
{
    protected $em;
    protected $container;

    public function __construct(EntityManagerInterface $entityManager,
                                ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    public function ReturnArticles($request)
    {
        $em = $this->em;
        $container = $this->container;
        $query = $em->createQuery(
            '
                SELECT
                        t.id,
                        t.author,
                        t.title,
                        t.content,
                        t.created_at,
                        t.updated_at,
                        t.likes_count,
                        t.image
                FROM
                    App\Entity\Articles t ORDER BY t.created_at DESC
            '
        );
        $paginator = $container->get('knp_paginator');
        $result = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 1)
        );
        return ($result);
    }

    public function ReturnPopularArticles($request)
    {
        $em = $this->em;
        $container = $this->container;
        $query = $em->createQuery(
            '
                SELECT
                        t.id,
                        t.author,
                        t.title,
                        t.content,
                        t.created_at,
                        t.updated_at,
                        t.likes_count,
                        t.image
                FROM
                    App\Entity\Articles t
                ORDER BY t.created_at, t.likes_count DESC
            '
        );
        $paginator = $container->get('knp_paginator');
        $result = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 1)
        );
        return ($result);
    }

}