<?php

namespace App\Controller\Back;

use App\Controller\FileException;
use App\Entity\Video;
use App\Form\VideoType;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;

#[Route('/video')]
class VideoController extends AbstractController
{
    #[Route('/', name: 'app_video_index', methods: ['GET'])]
    public function index(VideoRepository $videoRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('back/video/index.html.twig', [
            'videos' => $videoRepository->findAll(),
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/new', name: 'app_video_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, VideoRepository $videoRepository, MailerInterface $mailer, UserRepository $userRepository, UserInterface $user_admin): Response
    {
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tags = $form->get("tags")->getData();
            foreach($tags as $tag){
                $video->addTag($tag);
            }
            // Handle file upload
            $videoFile = $form->get('video')->getData();

            if ($videoFile) {
                $originalFilename = pathinfo($videoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$videoFile->guessExtension();

                try {
                    $videoFile->move(
                        $this->getParameter('videos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle file upload error
                }

                $video->setCreatedAt(new \DateTimeImmutable('now'));
                $video->setVideo($newFilename);
                $users = $entityManager->getRepository(User::class)->findAll();
                foreach ($users as $user) {
                    if($user->getIsNotify()){
                        $email = (new TemplatedEmail())
                        ->from(new Address('hackathon.groupe3@gmail.com', 'VCivuqQm2gvAJXu'))
                        ->to($user->getEmail())
                        ->subject('Nouvel article')
                        ->htmlTemplate('mailer/video_mail.html.twig')
                        ->context([
                            'video' => $video,
                            'user' => $user,
                        ]);
        
                        $mailer->send($email);
                    }
                }
            }

            $videoRepository->save($video, true);

            return $this->redirectToRoute('back_app_video_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/video/new.html.twig', [
            'video' => $video,
            'form' => $form,
            'user_admin' => $userRepository->findBy(array('id' => $user_admin->getId())),
        ]);
    }

    #[Route('/{id}', name: 'app_video_show_details', methods: ['GET'])]
    public function show(Video $video, UserRepository $userRepository, UserInterface $user): Response
    {
        return $this->render('back/video/show.html.twig', [
            'video' => $video,
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/video/{id}/show', name: 'app_video_show_video', methods: ['GET'])]
    public function showVideo(Video $video): Response
    {
        $videoPath = $this->getParameter('videos_directory').'/'.$video->getVideo();

        // Check if the video file exists
        if (!file_exists($videoPath)) {
            throw $this->createNotFoundException('The video file does not exist');
        }

        // Create a response containing the video file
        $response = new BinaryFileResponse($videoPath);
        $response->headers->set('Content-Type', 'video/mp4');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $video->getTitle().'.mp4');

        return $response;
    }

    #[Route('/{id}/edit', name: 'app_video_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Video $video, VideoRepository $videoRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $videoRepository->save($video, true);

            return $this->redirectToRoute('back_app_video_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/video/edit.html.twig', [
            'video' => $video,
            'form' => $form,
            'user_admin' => $userRepository->findBy(array('id' => $user->getId())),
        ]);
    }

    #[Route('/{id}', name: 'app_video_delete', methods: ['POST'])]
    public function delete(Request $request, Video $video, VideoRepository $videoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$video->getId(), $request->request->get('_token'))) {
            $videoRepository->remove($video, true);
        }

        return $this->redirectToRoute('back_app_video_index', [], Response::HTTP_SEE_OTHER);
    }
}
