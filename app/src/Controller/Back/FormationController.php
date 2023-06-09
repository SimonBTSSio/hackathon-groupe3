<?php

namespace App\Controller\Back;

use App\Entity\Formation;
use App\Entity\Chapter;
use App\Form\FormationType;
use App\Form\ChapterType;
use App\Repository\FormationRepository;
use App\Repository\ChapterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FileUploader;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/formation')]
class FormationController extends AbstractController
{
    #[Route('/', name: 'formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('formation/admin/index.html.twig', [
            'formations' => $formationRepository->findAll(),
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/new', name: 'formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FormationRepository $formationRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            if ($image) {
                $fileUploader = new FileUploader($this->getParameter('formation_img'));
                $fileName = $fileUploader->upload($image);

                if($fileName) {
                    $formation->setImage($fileName);
                }
                else {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de l\'image');
                }
            }

            $formation->setCreatedAt(new \DateTimeImmutable());
            $formation->setUpdatedAt(new \DateTimeImmutable());

            $formationRepository->save($formation, true);

            return $this->redirectToRoute('back_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('formation/admin/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}', name: 'formation_show', methods: ['GET'])]
    public function show(Formation $formation, UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('formation/admin/show.html.twig', [
            'formation' => $formation,
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}/edit', name: 'formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Formation $formation, FormationRepository $formationRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formationRepository->save($formation, true);

            return $this->redirectToRoute('back_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('formation/admin/edit.html.twig', [
            'formation' => $formation,
            'form' => $form,
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}', name: 'formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, FormationRepository $formationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formation->getId(), $request->request->get('_token'))) {
            $formationRepository->remove($formation, true);
        }

        return $this->redirectToRoute('back_formation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/new_chapter', name:'formation_new_chapter')]
    public function newChapter(Request $request, Formation $formation, ChapterRepository $chapterRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        $chapter = new Chapter();

        if(!$formation) {
            throw $this->createNotFoundException('La formation n\'existe pas');
        }

        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chapter->setCreatedAt(new \DateTimeImmutable());
            $chapter->setUpdatedAt(new \DateTimeImmutable());

            $chapter->setFormation($formation);

            $chapterRepository->save($chapter, true);

            return $this->redirectToRoute('back_formation_show', ['id' => $formation->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chapter/admin/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }
}
