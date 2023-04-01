<?php

namespace App\Controller\Back;

use App\Entity\Quizz;
use App\Entity\Question;
use App\Form\QuizzType;
use App\Form\QuestionType;
use App\Repository\QuizzRepository;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/quizz')]
class QuizzController extends AbstractController
{
    #[Route('/', name: 'quizz_index', methods: ['GET'])]
    public function index(QuizzRepository $quizzRepository): Response
    {
        return $this->render('quizz/admin/index.html.twig', [
            'quizzs' => $quizzRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'quizz_new', methods: ['GET', 'POST'])]
    public function new(Request $request, QuizzRepository $quizzRepository): Response
    {
        $quizz = new Quizz();
        
        $form = $this->createForm(QuizzType::class, $quizz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quizzRepository->save($quizz, true);

            return $this->redirectToRoute('back_quizz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('quizz/admin/new.html.twig', [
            'quizz' => $quizz,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'quizz_show', methods: ['GET'])]
    public function show(Quizz $quizz): Response
    {
        return $this->render('quizz/admin/show.html.twig', [
            'quizz' => $quizz,
        ]);
    }

    #[Route('/{id}/edit', name: 'quizz_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quizz $quizz, QuizzRepository $quizzRepository): Response
    {
        $form = $this->createForm(QuizzType::class, $quizz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quizzRepository->save($quizz, true);

            return $this->redirectToRoute('back_quizz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('quizz/admin/edit.html.twig', [
            'quizz' => $quizz,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'quizz_delete', methods: ['POST'])]
    public function delete(Request $request, Quizz $quizz, QuizzRepository $quizzRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quizz->getId(), $request->request->get('_token'))) {
            $quizzRepository->remove($quizz, true);
        }

        return $this->redirectToRoute('back_quizz_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/add-question', name: 'quizz_add_question', methods: ['GET', 'POST'])]
    public function addQuestion(Request $request, Quizz $quizz, QuestionRepository $questionRepository): Response
    {
        $question = new Question();

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setQuizz($quizz);

            $questionRepository->save($question, true);

            return $this->redirectToRoute('back_quizz_show', ['id' => $quizz->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('question/admin/new.html.twig', [
            'quizz' => $quizz,
            'form' => $form,
        ]);
    }
}
