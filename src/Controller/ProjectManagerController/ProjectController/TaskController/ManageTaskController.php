<?php

namespace App\Controller\ProjectManagerController\ProjectController\TaskController;

use App\Repository\ProjectDetailsRepository;
use App\Repository\TaskWithProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use  Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManageTaskController extends AbstractController
{
    #[Route('/ProjectManager/Dashboard/Projects/Tasks', name: 'manage_task')]
    public function index(Request $request, TaskWithProjectRepository $taskWithProjectRepository, ProjectDetailsRepository $projectDetailsRepository, EntityManagerInterface $entityManager): Response
    {
        $page = $request->query->get('page', 1);
        $pid = $request->query->get("id");
        $project = $projectDetailsRepository->findBy(["id" => $request->query->get("id"), "ProjectManager" => $this->getUser()]);
        if ($project) {
            $taskArray = $taskWithProjectRepository->findAllTaskForProjectQueryBuilder($pid);
            $adapter = new QueryAdapter($taskArray);
            $pagerfantaTaskArray = Pagerfanta::createForCurrentPageWithMaxPerPage(
                $adapter,
                $page,
                10
            );
            return $this->render("projectManager/taskOperations/all_task.html.twig", [
                'project' => $project[0],
                'taskArray' => $pagerfantaTaskArray,
                'pagerfanta' => $pagerfantaTaskArray
            ]);
        } else {
            $this->addFlash("warning", "You are Not Allow to See Other Task");
            return $this->redirectToRoute("app_project_manager_projects");
        }
    }
}
