<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CentresInteretType;

#[Route('/centres-interet', name: 'ci_')]
class CentresInteretController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(UserInterface $user, Request $request, UserRepository $userRepository): Response
    {
        $form = $this->createForm(CentresInteretType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('front_default_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/ci/new.html.twig', [
            'user' => $userRepository->findBy(array('id' => $user->getId())),
            'form' => $form,
        ]);
    }
}
