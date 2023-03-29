<?php

namespace App\Controller\Front;

use App\Entity\Visite;
use App\Form\VisiteType;
use App\Repository\VisiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/visite')]
class VisiteController extends AbstractController
{
    #[Route('/', name: 'visite_index', methods: ['GET'])]
    public function index(VisiteRepository $visiteRepository): Response
    {
        return $this->render('front/visite/index.html.twig', [
            'visites' => $visiteRepository->findAll(),
        ]);
    }

    #[Route('/visite_prevention', name: 'visite_prevention', methods: ['GET'])]
    public function prevention(VisiteRepository $visiteRepository): Response
    {
        $visites = $visiteRepository->findBy(array('user' => $this->getUser()));

        return $this->render('front/visite/prevention.html.twig', [
            'visites' => $visites,
        ]);
    }

    #[Route('/new', name: 'new_visite', methods: ['GET', 'POST'])]
    public function new(Request $request, VisiteRepository $visiteRepository): Response
    {
        $visite = new Visite();
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);
        $visite->setUser($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $visiteRepository->save($visite, true);

            return $this->redirectToRoute('front_visite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/visite/new.html.twig', [
            'visite' => $visite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'visite_show', methods: ['GET'])]
    public function show(Visite $visite): Response
    {
        return $this->render('front/visite/show.html.twig', [
            'visite' => $visite,
        ]);
    }

    #[Route('/{id}/edit', name: 'visite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Visite $visite, VisiteRepository $visiteRepository): Response
    {
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $visiteRepository->save($visite, true);

            return $this->redirectToRoute('visite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/visite/edit.html.twig', [
            'visite' => $visite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visite_delete', methods: ['POST'])]
    public function delete(Request $request, Visite $visite, VisiteRepository $visiteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$visite->getId(), $request->request->get('_token'))) {
            $visiteRepository->remove($visite, true);
        }

        return $this->redirectToRoute('visite_index', [], Response::HTTP_SEE_OTHER);
    }

}
