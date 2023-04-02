<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FormationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Formation;
use App\Entity\Chapter;

#[Route('/formation')]
class FormationController extends AbstractController
{
    #[Route('/', name: 'formations', methods: ['GET'])]
    public function index(FormationRepository $formationRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('formation/front/index.html.twig', [
            'formations' => $formationRepository->findAll(),
            'user' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}', name: 'formation_show', methods: ['GET'])]
    public function show(Formation $formation, UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('formation/front/show.html.twig', [
            'formation' => $formation,
            'user' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }
}
