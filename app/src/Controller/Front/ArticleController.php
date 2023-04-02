<?php

namespace App\Controller\Front;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tag;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Service\FileUploader;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, UserInterface $user, UserRepository $userRepository): Response
    {
        $articles = $entityManager
            ->getRepository(Article::class)
            ->findAll();

        return $this->render('front/article/index.html.twig', [
            'articles' => $articles,
            'user' => $userRepository->findBy(array('id' => $user->getId()))
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article, UserInterface $user, UserRepository $userRepository): Response
    {
        return $this->render('front/article/show.html.twig', [
            'article' => $article,
            'user' => $userRepository->findBy(array('id' => $user->getId()))
        ]);
    }
}
