<?php

namespace App\Controller\FileHandler;

use App\Entity\ProjectDetails;
use App\Entity\User;
use App\Entity\UserProfilePhoto;
use App\Repository\UserProfilePhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

class FileUploadHandler
{
    public function __construct(private String $uploadPath, private EntityManagerInterface $entityManager, private UserProfilePhotoRepository $userProfilePhotoRepository)
    {
        $this->uploadPath = $uploadPath;
        $this->entityManager = $entityManager;
        $this->userProfilePhotoRepository = $userProfilePhotoRepository;
    }

    public function UpdateProfileImage(UploadedFile $newProfileImage, User $user,)
    {
        $projectRoot = $this->uploadPath;
        if (is_dir($projectRoot . "/public")) {
            if (!is_dir($projectRoot . "/public/upload")) {
                mkdir($projectRoot . "/public/upload");
            }
            $userId = $user->getId();
            if (!is_dir($projectRoot . "/public/upload/$userId")) {
                mkdir($projectRoot . "/public/upload/$userId");
            }
            $newProfileName = "Profile" . $userId . "." . $newProfileImage->getClientOriginalExtension();
            if ($newProfileImage->move($projectRoot . "/public/upload/$userId", $newProfileName)) {

                $userProfile = $this->userProfilePhotoRepository->findBy(['user' => $user]);
                if (!$userProfile)
                    $userProfile = new UserProfilePhoto();
                else
                    $userProfile = $userProfile[0];

                $userProfile->setUser($user);
                $userProfile->setSource($newProfileName);
                try {
                    $this->entityManager->persist($userProfile);
                    $this->entityManager->flush();
                    return ['status' => true,'message'=> "Profile photo updated"];
                } catch (Throwable $t) {
                    return ['status' => false, 'message' => "opps!! some error occur while updated profile " . $t];
                }
            }
            else{
                return ['status' => false,'message'=> "File Not Move"];

            }
        }
    }
    public function UploadProjectAttachment(UploadedFile $projectAttachment, ProjectDetails $projectDetails)
    {
        $projectRoot = $this->uploadPath;
        if (is_dir($projectRoot . "/public")) {
            if (!is_dir($projectRoot . "/public/upload")) {
                mkdir($projectRoot . "/public/upload");
            }
            $projectId = $projectDetails->getId();
            if (!is_dir($projectRoot . "/public/upload/projects")) {
                mkdir($projectRoot . "/public/upload/projects");
            }
            $newProjectAttachmentName = "Project-" . $projectId . "." . $projectAttachment->getClientOriginalExtension();
            if ($projectAttachment->move($projectRoot . "/public/upload/projects", $newProjectAttachmentName)) {
                $projectDetails->setAttachment($newProjectAttachmentName);
                try {
                    $this->entityManager->persist($projectDetails);
                    $this->entityManager->flush();
                    return ['status' => true,'message'=> "Attachment Uploaded"];
                } catch (Throwable $t) {
                    return ['status' => false, 'message' => "opps!! some error occur while Upload File " . $t];
                }
            }
            else{
                return ['status' => false,'message'=> "File Not Move"];

            }
        }
    }
}
