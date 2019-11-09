<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Like;
use App\Entity\Users;
use App\Form\ArticleType;
use App\Service\ArticleService;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="articles")
     * @param Request $request
     * @param ArticleService $query
     * @return Response
     */
    public function index(Request $request, ArticleService $query)
    {
        $articles = $query->ReturnArticles($request);
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
        ]);
    }


    /**
     * @Route("/my_articles/{page?1}", name="my_articles")
     * @param Security $security
     * @param Users $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function privatePage(int $page, ContainerInterface $container): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(Users::class)->findOneBy([
            'id' => $user,
        ]);

        $paginator = $container->get('knp_paginator');

        $userArticles = $paginator->paginate(
            $user->getArticles(),
            $page,
            1
        );

        return $this->render('article/myArticles.html.twig', [
            'user' => $userArticles,
        ]);
        // ... do whatever you want with $user
    }

    /**
     * @Route("/blogger_articles/{email}/{page?1}", name="blogger_articles")
     * @param int $page
     * @param Users $user
     * @param ContainerInterface $container
     * @return Response
     */
    public function bloggerArticles(int $page, Users $user, ContainerInterface $container): Response
    {

        //$user->getArticles();

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(Users::class)->findOneBy([
            'id' => $user,
        ]);

        $paginator = $container->get('knp_paginator');

        $userArticles = $paginator->paginate(
            $user->getArticles(),
            $page,
            1
        );

        return $this->render('article/bloggerArticles.html.twig', [
            'user' => $userArticles,
        ]);
    }

    /**
     * @Route("/article/single/{article}/{page?1}", name="single_article")
     * @param int $page
     * @param Articles $article
     * @param ContainerInterface $container
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function single(int $page, Articles $article, ContainerInterface $container)
    {
//        $article = $this->getDoctrine()->getRepository(Articles::class)->findArticleWithComments($article->getId());
//        dump($article); exit;
//        dump($article); exit;
//        $comments = $article->getComments();
//
//        if($comments) {
//
//        }

        $paginator = $container->get('knp_paginator');

        $comments = $paginator->paginate(
            $article->getComments(),
            $page,
            1
        );


        return $this->render('article/single.html.twig', [
            'article' => $article,
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/article/create", name="create_article")
     */
    public function create(Request $request, FileUploader $fileUploader)
    {
        $article = new Articles();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $image = $form['image']->getData();
            if ($image) {
                $imageFileName = $fileUploader->upload($image);
                $article->setImage($imageFileName);
            }
            $article->setCreatedAt(new \DateTime('now'));
            $article->setAuthor($user->getEmail());
            $article->setUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            return $this->redirectToRoute('articles');
        }
        return $this->render('article/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/article/update/{article}", name="update_article")
     * @param Request $request
     * @param Articles $article
     * @param FileUploader $fileUploader
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function update(Request $request, Articles $article, FileUploader $fileUploader)
    {
        $form = $this->createForm(ArticleType::class, $article, [
            'action' => $this->generateUrl('update_article', [
                'article' => $article->getId()
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $articles = $form->getData();
            $image = $form['image']->getData();
            if ($image) {
                $imageFileName = $fileUploader->upload($image);
                $articles->setImage($imageFileName);
            }
            $articles->setUpdatedAt(new \DateTime('now'));
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('articles');
        }
        return $this->render('article/form.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/article/delete/{article}", name="article_delete")
     */
    public function delete(Articles $article)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        return $this->redirectToRoute('articles');
    }

    /**
     * @Route("/like/{article}/{page}", name="like")
     * @param AuthorizationCheckerInterface $authChecker
     * @param Articles $article
     * @param ContainerInterface $container
     * @return Response
     */
    public function like(int $page = 1, AuthorizationCheckerInterface $authChecker, Articles $article, ContainerInterface $container)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (false === $authChecker->isGranted("IS_AUTHENTICATED_FULLY")) {
            throw new AccessDeniedException('Unable to access this page!');
        }


        $isLiked = $this->getDoctrine()->getRepository(Like::class)->findOneBy([
            'user_id' => $user,
            'article_id' => $article
        ]);

        if (null === $isLiked) {
            $like = new Like();
            $like->setUser($user);
            $like->setArticle($article);
            $likeCount = $article->getLikesCount();
            $article->setLikesCount($likeCount + 1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($like);
            $em->persist($article);
            $em->flush();
        }

        $paginator = $container->get('knp_paginator');

        $comments = $paginator->paginate(
            $article->getComments(),
            $page,
            1
        );

        return $this->render('article/single.html.twig', [
            'article' => $article,
            'comments' => $comments
        ]);

    }

    /**
     * @Route("/administration/articles", name="administration_articles")
     * @param Request $request
     * @param ArticleService $query
     * @return Response
     */
    public function administrationArticles(Request $request, ArticleService $query)
    {
        $articles = $query->ReturnArticles($request);

        return $this->render('article/articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/popular", name="popular")
     * @param Request $request
     * @param ArticleService $query
     * @return Response
     */
    public function popularArticles(Request $request, ArticleService $query)
    {
        $articles = $query->ReturnPopularArticles($request);

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }


    /**
     * @Route("/lang/{lang}", name="lang")
     * @param string $lang
     * @param AuthorizationCheckerInterface $authChecker
     * @return Response
     */
    public function lang(string $lang, AuthorizationCheckerInterface $authChecker)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (false === $authChecker->isGranted("IS_AUTHENTICATED_FULLY")) {
            throw new AccessDeniedException('Unable to access this page!');
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(Users::class)->findOneBy([
            'id' => $user,
        ]);
        $user->setLocale($lang);
        $em->persist($user);
        $em->flush();

       return new Response('<h1>Your language is: '.$lang.'</h1>');
    }
}

