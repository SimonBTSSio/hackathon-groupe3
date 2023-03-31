<?php

namespace App\Controller\Back;

use App\Entity\Chapter;
use App\Entity\Formation;
use App\Form\ChapterType;
use App\Repository\ChapterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/chapter')]
class ChapterController extends AbstractController
{
    #[Route('/', name: 'chapter_index', methods: ['GET'])]
    public function index(ChapterRepository $chapterRepository): Response
    {
        return $this->render('chapter/admin/index.html.twig', [
            'chapters' => $chapterRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'chapter_show', methods: ['GET'])]
    public function show(Chapter $chapter, ChapterRepository $chapterRepository): Response
    {
        return $this->render('chapter/admin/show.html.twig', [
            'chapter' => $chapter,
        ]);
    }

    #[Route('/{id}/edit', name: 'chapter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chapter $chapter, ChapterRepository $chapterRepository): Response
    {
        $formation = $chapter->getFormation();
        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $chapter->setFormation($formation);
            $chapter->setUpdatedAt(new \DateTimeImmutable());
            
            $chapterRepository->save($chapter, true);

            return $this->redirectToRoute('back_formation_show', ['id' => $formation->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chapter/admin/edit.html.twig', [
            'chapter' => $chapter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'chapter_delete', methods: ['POST'])]
    public function delete(Request $request, Chapter $chapter, ChapterRepository $chapterRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chapter->getId(), $request->request->get('_token'))) {
            $chapterRepository->remove($chapter, true);
        }

        return $this->redirectToRoute('chapter_index', [], Response::HTTP_SEE_OTHER);
    }
}
