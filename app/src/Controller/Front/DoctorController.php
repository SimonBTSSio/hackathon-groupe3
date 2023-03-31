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
use App\Form\BackUserType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use App\Form\ConsultDoctorType;

#[Route('/doctor', name: 'doctor_')]
class DoctorController extends AbstractController
{
    #[Route('/', name: 'default_index')]
    public function index(UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('front/doctor/index.html.twig', [
            'doctors' => $userRepository->findDoctors(),
            'user' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}/consulter', name: 'consult', methods: ['GET', 'POST'])]
    public function consult(User $user, Request $request, UserRepository $userRepository, $id, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ConsultDoctorType::class);
        $form->handleRequest($request);
        $doctor = $userRepository->findOneBy(array('id' => $id));

        if ($form->isSubmitted() && $form->isValid()) {
            //send email to hero
            $email = (new TemplatedEmail())
                ->from(new Address($this->getUser()->getEmail(), 'Consultation'))
                ->to($doctor->getEmail())
                ->subject('Consultation')
                ->htmlTemplate('mailer/consult_doctor.html.twig')
                ->context([
                    'content' => $form->get('description')->getData(),
                    'user' => $this->getUser(),
                    'doctor' => $userRepository->findOneBy(array('id' => $id)),
                ]);

            $mailer->send($email);


            return $this->redirectToRoute('front_default_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/doctor/assign.html.twig', [
            'doctor' => $userRepository->findOneBy(array('id' => $id)),
            'form' => $form,
        ]);
    }
}
