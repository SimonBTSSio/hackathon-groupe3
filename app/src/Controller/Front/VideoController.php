<?php

namespace App\Controller\Back;

use App\Entity\Video;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/video')]
class VideoController extends AbstractController
{
    #[Route('/', name: 'app_video_index', methods: ['GET'])]
    public function index(VideoRepository $videoRepository): Response
    {
        return $this->render('front/video/index.html.twig', [
            'videos' => $videoRepository->findAll(),
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

    #[Route('/{id}', name: 'app_video_show_details', methods: ['GET'])]
    public function show(Video $video): Response
    {
        return $this->render('front/video/show.html.twig', [
            'video' => $video,
        ]);
    }
}
