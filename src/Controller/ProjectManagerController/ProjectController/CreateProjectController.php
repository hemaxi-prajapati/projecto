<?php

namespace App\Controller\ProjectManagerController\ProjectController;

use App\Controller\FileHandler\FileUploadHandler;
use App\Entity\ProjectDetails;
use App\Form\Project\CreateProjectType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;


class CreateProjectController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    //create project page 

    #[Route('/ProjectManager/Dashboard/Projects/Create', name: 'app_create_project')]
    public function createProject(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, UserRepository $userRepository, FileUploadHandler $fileUploadHandler): Response
    {

        $project = new ProjectDetails();
        $createProjectForm = $this->createForm(CreateProjectType::class, $project);

        $createProjectForm->handleRequest($request);

        if ($createProjectForm->isSubmitted() && $createProjectForm->isValid()) {
            if ($createProjectForm['projectAttenchment']->getData()) {

                $fileConstraints = new File([
                    'maxSize' => '1M',
                    'maxSizeMessage' => 'The file is too big',
                    'mimeTypes' => ['pdf' => 'application/pdf'],
                    'mimeTypesMessage' => 'The format is incorrect, only PDF allowed'
                ]);
                $violations = ($validator->validate($createProjectForm['projectAttenchment']->getData(), $fileConstraints));
                if ($violations->count() > 0) {
                    $this->addFlash('warning', $violations[0]->getMessage());
                    return $this->render('projectManager/projectOperations/create_project.html.twig', [
                        'createProjectForm' => $createProjectForm->createView(),
                    ]);
                }
            }
            $project->setProjectManager($userRepository->find($this->security->getUser()));
            try {
                $entityManager->persist($project);
                $entityManager->flush();
                if ($createProjectForm['projectAttenchment']->getData()) {
                    $AttechFile = $fileUploadHandler->UploadProjectAttachment($createProjectForm['projectAttenchment']->getData(), $project);
                    $message = $AttechFile['message'];
                    $this->addFlash('success', 'Project created  And ' . $message);
                } else
                    $this->addFlash('success', 'Project Created ');
            } catch (Throwable $t) {
                $this->addFlash('warning', 'Opps Some Error Occurs :' . $t);
            }
            return $this->redirectToRoute('app_project_manager_projects', ['count' => true]);
        }
        return $this->render('projectManager/projectOperations/create_project.html.twig', [
            'createProjectForm' => $createProjectForm->createView(),
        ]);
    }
}