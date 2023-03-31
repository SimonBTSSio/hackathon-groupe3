<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file): string | bool
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $fileExtension = $file->guessExtension();

        if(!in_array($fileExtension, ['jpg', 'jpeg', 'png'])){
            return false;
        }

        //verify file size
        if($file->getSize() > 1000000){
            return false;
        }

        $newFilename = $safeFilename . '-' . uniqid('', true) . '.' . $file->guessExtension();

        try {
            $file->move(
                $this->getTargetDirectory(),
                $newFilename
            );
            return $newFilename;
        } catch (FileException $e) {
           return $e->getMessage();
        }

    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}