<?php

namespace App\Controller\Front;

use App\Entity\Categorie;
use App\Entity\Visite;
use App\Entity\Medecin;
use App\Form\CategorieType;
use App\Form\MedecinType;
use App\Form\VisiteType;
use App\Repository\CategorieRepository;
use App\Repository\MedecinRepository;
use App\Repository\VisiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/visite')]
class VisiteController extends AbstractController
{
    #[Route('/', name: 'app_visite_index', methods: ['GET'])]
    public function index(VisiteRepository $visiteRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('front/visite/index.html.twig', [
            'visites' => $visiteRepository->findAll(),
            'user' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/visite_prevention', name: 'visite_prevention', methods: ['GET'])]
    public function prevention(VisiteRepository $visiteRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        $visites = $visiteRepository->findBy(array('user' => $this->getUser()));

        return $this->render('front/visite/prevention.html.twig', [
            'visites' => $visites,
            'user' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/new', name: 'new_visite', methods: ['GET', 'POST'])]
    public function new(Request $request, VisiteRepository $visiteRepository, CategorieRepository $categorieRepository, MedecinRepository $medecinRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        $visite = new Visite();
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        $categorie = new Categorie();
        $formCategorie = $this->createForm(CategorieType::class, $categorie);
        $formCategorie->handleRequest($request);
        $medecin = new Medecin();
        $formMedecin = $this->createForm(MedecinType::class,$medecin);
        $formMedecin->handleRequest($request);

        $categorie->addMedecin($medecin);
        $visite->setUser($this->getUser());
        $visite->setCategorie($categorie);

        if ($form->isSubmitted() && $form->isValid() && $formCategorie->isSubmitted() && $formCategorie->isValid() && $formMedecin->isSubmitted() && $formMedecin->isValid()) {
            $medecinRepository->save($medecin, true);
            $categorieRepository->save($categorie, true);
            $visiteRepository->save($visite, true);

            return $this->redirectToRoute('front_app_visite_index', [], Response::HTTP_SEE_OTHER);
        }


        return $this->renderForm('front/visite/new.html.twig', [
            'visite' => $visite,
            'form' => $form,
            'formCategorie' => $formCategorie,
            'formMedecin' => $formMedecin,
            'user' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}', name: 'visite_show', methods: ['GET'])]
    public function show(Visite $visite, UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('front/visite/show.html.twig', [
            'visite' => $visite,
            'user' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}/edit', name: 'visite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Visite $visite, VisiteRepository $visiteRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $visiteRepository->save($visite, true);

            return $this->redirectToRoute('front_visite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/visite/edit.html.twig', [
            'visite' => $visite,
            'form' => $form,
            'user' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}', name: 'app_visite_delete', methods: ['POST'])]
    public function delete(Request $request, Visite $visite, VisiteRepository $visiteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$visite->getId(), $request->request->get('_token'))) {
            $visiteRepository->remove($visite, true);
        }

        return $this->redirectToRoute('front_visite_index', [], Response::HTTP_SEE_OTHER);
    }

}
