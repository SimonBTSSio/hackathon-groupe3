<?php

namespace App\Controller\Back;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FileUploader;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager
            ->getRepository(Article::class)
            ->findAll();

        return $this->render('back/article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        $tags = $entityManager->getRepository(Tag::class)->findAll();
        $tagsArray = array_map(function($tag) {
            return [$tag->getId() => $tag->getLabel()];
        }, $tags);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            $tags = $form->get("tags")->getData();
            foreach($tags as $tag){
                $article->addTag($tag);
            }

            $fileUploader = new FileUploader($this->getParameter('img'));
            $newFilename = $fileUploader->upload($image);
            if($newFilename) {
                $article->setImg($newFilename);

            } else {
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de l\'image');
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('back_app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/article/new.html.twig', [
            'article' => $article,
            'form' => $form,
            'tags' => $tagsArray
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('back/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('back_app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
