<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Users;
use App\Form\ArticleType;
use App\Repository\ArticlesRepository;
use http\Client\Curl\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="articles")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $articles = $em->getRepository(Articles::class)->findAll();

        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
        ]);
    }


//    /**
//     * @Route("/articles/my/{user}", name="my_articles")
//     * @param Security $security
//     * @param Users $user
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function privatePage( Security $security,  Users $user) : Response
//    {
//       //$user = $security->getUser();
//        $em = $this->getDoctrine()->getManager();
//
//
//        return $this->render('article/myArticles.html.twig', [
//            'user' => $user,
//        ]);
//        // ... do whatever you want with $user
//    }

    /**
     * @Route("/my_articles", name="my_articles")
     * @param Security $security
     * @param Users $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function privatePage() : Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();


        return $this->render('article/myArticles.html.twig', [
            'user' => $user,
        ]);
        // ... do whatever you want with $user
    }

    /**
     * @Route("/article/single/{article}", name="single_article")
     * @param Articles $article
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function single(Articles $article)
    {
//        $article = $this->getDoctrine()->getRepository(Articles::class)->findArticleWithComments($article->getId());
//        dump($article); exit;
//        dump($article); exit;
//        $comments = $article->getComments();
//
//        if($comments) {
//
//        }


        return $this->render('article/single.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/article/create", name="create_article")
     */
    public function create(Request $request)
    {
        $article = new Articles();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $article->setCreatedAt(new \DateTime('now'));
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
     * @param Users $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function update(Request $request, Articles $article)
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
}
