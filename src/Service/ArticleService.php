<?php


namespace App\Service;
use App\Entity\Articles;
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
        //$query = $em->getRepository(Articles::class)->findAllArticles();

        //$result = $query->execute();
        $paginator = $container->get('knp_paginator');
        $result = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 1)
        );
//        dump($result); exit;
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
        //$result = $query->execute();
//        $query = $em->getRepository(Articles::class)->findPopularArticles();
        $paginator = $container->get('knp_paginator');
        $result = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 1)
        );
        return ($result);
    }

}