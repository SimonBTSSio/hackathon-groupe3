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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/quizz')]
class QuizzController extends AbstractController
{
    #[Route('/', name: 'quizz_index', methods: ['GET'])]
    public function index(QuizzRepository $quizzRepository, UserInterface $user, UserRepository $userRepository): Response
    {
        return $this->render('front/quizz/index.html.twig', [
            'quizzs' => $quizzRepository->findAll(),
            'user' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}', name: 'quizz_show')]
    public function show(Request $request, Quizz $quizz, ReponseRepository $reponseRepository, UserRepository $userRepository, UserInterface $user): Response
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
                    'choices' => $choices,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez choisir une réponse',
                        ]),
                    ],
                ]);
            }
            elseif($question->getType() == 'multiple') {
                $formBuilder->add('question_' . $question->getId(), ChoiceType::class, [
                    'label' => $question->getText(),
                    'expanded' => true,
                    'required' => true,
                    'choices' => $choices,
                    'multiple' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez choisir une réponse',
                        ]),
                    ],
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
                $userReponse = $data['question_' . $question->getId()];
                $reponses = is_array($userReponse) ? $userReponse : [$userReponse];

                $correct = true;

                if($question->getType() == 'unique') {
                    $correct = $reponseRepository->findOneBy([
                        'id' => $reponses[0],
                        'isCorrect' => true,
                    ]) ? true : false;

                    if($correct) {
                        $score++;
                    }
                }
                
                elseif($question->getType() == 'multiple') {
                    $correctAnswers = $reponseRepository->countBy(['question' => $question, 'isCorrect' => true]);
                    $correctSelected = 0;
                    foreach ($reponses as $reponse) {
                        $reponseCorrecte = $reponseRepository->findOneBy([
                            'id' => $reponse,
                            'isCorrect' => true,
                        ]);
                        if ($reponseCorrecte) {
                            $correctSelected++;
                        }
                        else {
                            $correctSelected = 0;
                            break; // Sortir de la boucle si une réponse incorrecte est trouvée
                        }
                    }
                    if($correctSelected == $correctAnswers) {
                        $score++;
                    }
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
            'maxScore' => count($questions),
            'user' => $userRepository->findBy(array('id' => $user->getId())),
            'questions' => $questions,
        ]);
    }
}
