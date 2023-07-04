<?php

namespace App\Controller\ProjectManagerController\ProjectController;

use App\Controller\FileHandler\FileUploadHandler;
use App\Form\Project\CreateProjectType;
use App\Form\Project\SingleProjectManageType;
use App\Repository\ProjectAssignmentRepository;
use App\Repository\ProjectDetailsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ManageProjectController extends AbstractController
{
    #[Route('/ProjectManager/Dashboard/Projects/manage', name: 'app_single_project_manage')]
    public function index(Request $request, UserRepository $userRepository, FileUploadHandler $fileUploadHandler, ProjectDetailsRepository $projectDetailsRepository, EntityManagerInterface $entityManager, ProjectAssignmentRepository $projectAssignmentRepository): Response
    {
        $project = $projectDetailsRepository->findBy(["id" => $request->query->get("id"), "ProjectManager" => $this->getUser()]);
        if ($project) {
            $project = $project[0];
            $form = $this->createForm(CreateProjectType::class, $project);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                try {
                    $entityManager->persist($project);
                    $entityManager->flush();
                    if ($form['projectAttenchment']->getData()) {
                        $AttechFile = $fileUploadHandler->UploadProjectAttachment($form['projectAttenchment']->getData(), $project);
                        $message = $AttechFile['message'];
                        $this->addFlash('success', 'Project Details Updated And ' . $message);
                    } else {
                        $this->addFlash('success', 'Project Details Updated');
                    }
                } catch (Throwable $t) {
                    $this->addFlash('warning', 'Opps Some Error Occurs :' . $t);
                };
                return $this->redirectToRoute('app_single_project_manage', ['id' => $project->getId()]);
            }

            $thisProjectAssignmentQueyBuilder = $projectAssignmentRepository->findAllInThisProjectQueryBuilder($project->getId());
            $adapter = new QueryAdapter($thisProjectAssignmentQueyBuilder);
            $ProjectAssignees = Pagerfanta::createForCurrentPageWithMaxPerPage(
                $adapter,
                $request->query->get('page', 1),
                10
            );
            return $this->render("projectManager/projectOperations/manage_project.html.twig", [
                'createProjectForm' => $form->createView(),
                'projectId' => $project->getId(),
                'projectAssign' => $ProjectAssignees
            ]);
        } else {
            $this->addFlash("warning", "You are Not Allow to See Other Projects");
            // dd($request);
            return $this->redirectToRoute("app_project_manager_projects");
        }
    }
}
