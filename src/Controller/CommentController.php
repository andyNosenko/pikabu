<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Comments;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comments/create/{article}", name="comment_create_form")
     */
    public function create(Request $request, Articles $article)
    {
        $comment = new Comments();
        $form = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('comment_create_form', [
                'article' => $article->getId()
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setCreatedAt(new \DateTime('now'));
            $comment->setArticle($article);
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            return $this->redirectToRoute('single_article', ['article' => $article->getId()]);
        }
        return $this->render('comment/form.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    }
    /**
     * @Route("/comments/update/{article}/{comment}", name="comment_update_form")
     */
    public function update(Request $request, Articles $article, Comments $comment)
    {
        $form = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('comment_update_form', [
                'article' => $article->getId(),
                'comment' => $comment->getId()
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setUpdatedAt(new \DateTime('now'));
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('single_article', ['article' => $article->getId()]);
        }
        return $this->render('comment/form.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    }
    /**
     * @Route("/comments/delete/{article}/{comment}", name="comment_delete")
     */
    public function delete(Articles $article, Comments $comment)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        return $this->redirectToRoute('single_article', ['article' => $article->getId()]);
    }

    /**
     * @Route("/comments/reply/{article}/{comment}", name="comment_reply")
     */
    public function reply(Request $request, Articles $article, Comments $comment)
    {
        $subComment = new Comments();
        $form = $this->createForm(CommentType::class, $subComment, [
            'action' => $this->generateUrl('comment_reply', [
                'article' => $article->getId(),
                'comment' => $comment->getId(),
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $subComment = $form->getData();
            $subComment->setCreatedAt(new \DateTime('now'));
           // $subComment->setArticle($article);
            $subComment->setParent($comment);
            $em = $this->getDoctrine()->getManager();
            $em->persist($subComment);
            $em->flush();
            return $this->redirectToRoute('single_article', ['article' => $article->getId()]);
        }
        return $this->render('comment/form.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    }
}
