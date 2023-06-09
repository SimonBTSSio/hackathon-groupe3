<?php

namespace App\Controller\Back;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\Tag;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FileUploader;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;

#[Route('/article')]
class ArticleController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, UserRepository $userRepository, UserInterface $user): Response
    {
        $articles = $entityManager
            ->getRepository(Article::class)
            ->findAll();

        return $this->render('back/article/index.html.twig', [
            'articles' => $articles,
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, UserRepository $userRepository, UserInterface $user_admin): Response
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

            $users = $entityManager->getRepository(User::class)->findAll();
            foreach ($users as $user) {
                if($user->getIsNotify()){
                    $email = (new TemplatedEmail())
                    ->from(new Address('hackathon.groupe3@gmail.com', 'VCivuqQm2gvAJXu'))
                    ->to($user->getEmail())
                    ->subject('Nouvel article')
                    ->htmlTemplate('mailer/article_mail.html.twig')
                    ->context([
                        'article' => $article,
                        'user' => $user,
                    ]);
    
                    $mailer->send($email);
                }
            }

            return $this->redirectToRoute('back_app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/article/new.html.twig', [
            'article' => $article,
            'form' => $form,
            'tags' => $tagsArray,
            'user_admin' => $userRepository->findBy(array('id' => $user_admin->getId())),
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article, UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('back/article/show.html.twig', [
            'article' => $article,
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, UserRepository $userRepository, UserInterface $user): Response
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
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
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
