<?php

namespace App\Controller\Front;

use App\Entity\Quizz;
use App\Entity\Question;
use App\Form\QuizzType;
use App\Form\QuestionType;
use App\Repository\QuizzRepository;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormBuilder;

#[Route('/quizz')]
class QuizzController extends AbstractController
{
    #[Route('/', name: 'quizz_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('front/quizz/index.html.twig', [
            'controller_name' => 'QuizzController',
        ]);
    }

    #[Route('/{id}', name: 'quizz_show')]
    public function show(Request $request, Quizz $quizz, ReponseRepository $reponseRepository): Response
    {
        $questions = $quizz->getQuestions();

        $formBuilder = $this->createFormBuilder();

        foreach ($questions as $question) {
            $choices = [];
            foreach ($question->getReponses() as $reponse) {
                $choices[$reponse->getText()] = $reponse->getId();
            }
            if($question->getType() == 'unique') {
                $formBuilder->add('question_' . $question->getId(), ChoiceType::class, [
                    'label' => $question->getText(),
                    'expanded' => true,
                    'required' => true,
                    'choices' => $choices
                ]);
            }
            elseif($question->getType() == 'multiple') {
                $formBuilder->add('question_' . $question->getId(), ChoiceType::class, [
                    'label' => $question->getText(),
                    'expanded' => true,
                    'required' => true,
                    'choices' => $choices,
                    'multiple' => true,
                ]);
            }
        }

        $formBuilder->add('submit', SubmitType::class, [
            'label' => 'Valider',
        ]);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $score = 0;

            foreach ($questions as $question){
                $reponses = $data['question_' . $question->getId()];
                $reponses = is_array($reponses) ? $reponses : [$reponses];

                $correct = true;

                if($question->getType() == 'unique') {
                    $correct = $reponseRepository->findOneBy([
                        'id' => $reponses[0],
                        'isCorrect' => true,
                    ]) ? true : false;
                }

                elseif($question->getType() == 'multiple') {
                    foreach ($reponses as $reponse) {
                        $correct = $reponseRepository->findOneBy([
                            'id' => $reponse,
                            'isCorrect' => true,
                        ]) ? true : false;
                    }
                }

                if($correct) {
                    $score++;
                }
            }

            if ($score == 0){
                $score = 'Aucune bonne réponse';
            }
            
        }

        return $this->render('front/quizz/show.html.twig', [
            'quizz' => $quizz,
            'form' => $form->createView(),
            'score' => $score ?? null,
        ]);
    }
}
