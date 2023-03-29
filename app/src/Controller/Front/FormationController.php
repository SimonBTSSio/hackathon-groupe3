<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FormationRepository;
use App\Entity\Formation;

#[Route('/formation')]
class FormationController extends AbstractController
{
    #[Route('/', name: 'formations', methods: ['GET'])]
    public function index(FormationRepository $formationRepository): Response
    {
        return $this->render('formation/front/index.html.twig', [
            'formations' => $formationRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('formation/front/show.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}/chapter/{chapter}', name: 'formation_chapter_show', methods: ['GET'])]
    public function showChapter(Formation $formation, Chapter $chapter, ChapterRepository $chapterRepository): Response
    {
        return $this->render('formation/front/chapter.html.twig', [
            'formation' => $formation,
            'chapter' => $chapter,
        ]);
    }
}
