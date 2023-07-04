<?php

namespace App\Controller\ProjectManagerController;

use App\Form\Project\FilterProjectByProjectNameType;
use App\Form\Project\FilterTaskType;
use App\Form\Task\FilterTaskType as TaskFilterTaskType;
use App\Repository\ProjectDetailsRepository;
use App\Repository\TaskWithProjectRepository;
use App\Repository\UserRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProjectManagerDashboardController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }



    // show all projects created by project manager 
    #[Route('/ProjectManager/Dashboard/Projects', name: 'app_project_manager_projects')]
    public function index(Request $request, ProjectDetailsRepository $projectDetailsRepository): Response
    {
        $page = $request->query->get('page', 1);
        $showProjectsQueryBuilder = $request->query->get("status") ? ($projectDetailsRepository->findProjectsByStatusAndProjectManagerQueryBuilder($this->getUser(), $request->query->get("status"))) : ($projectDetailsRepository->findAllProjectsQueryBuilder($this->getUser()));

        $form = $this->createForm(FilterProjectByProjectNameType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            ($data = $form->getData()['ProjectName']);
            if ($data) {
                $showProjectsQueryBuilder = $showProjectsQueryBuilder->andWhere("pd.Name LIKE :name ")
                    ->setParameter("name", '%' . $data . '%');
            }
            //     $data = $form->getData();
            //     if ($data['ProjectName']) {
            //         $showProjectsQueryBuilder = $showProjectsQueryBuilder->andWhere("pd.Name LIKE :name ")
            //             ->setParameter("name", '%' . $data['ProjectName'] . '%');
            //     }
            //     $page = 1;
        }

        $adapter = new QueryAdapter($showProjectsQueryBuilder);
        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            10

        );

        return $this->render("projectManager/all_projects.html.twig", [
            'projects' => $pagerfanta,
            'pagerFanta' => $pagerfanta,
            'filterForm' => $form->createView()
        ]);
    }


    // show all tasks of project under that PM
    #[Route('/ProjectManager/Dashboard/Tasks', name: 'app_project_manager_tasks')]
    public function task(Request $request, UserRepository $userRepository, ProjectDetailsRepository $projectDetailsRepository, TaskWithProjectRepository $taskWithProjectRepository)
    {
        $page = $request->query->get('page', 1);
        $projectManager = $this->getUser();
        $taskArray = $taskWithProjectRepository->findAllTaskForProjectManagerQueryBuilder($projectManager);
        $form = $this->createForm(TaskFilterTaskType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['searchByProjectName'] != null) {
                $taskArray = $taskArray
                    ->andWhere("pd.Name LIKE :name ")
                    ->setParameter("name", "%" . $data['searchByProjectName'] . "%");
            }
            if ($data['searchByEmployee'] != null) {
                $taskArray = $taskArray
                    ->join("t.users", "u")
                    ->andWhere('u.Firstname LIKE :name or u.LastName LIKE :name ')
                    ->setParameter("name", "%" . $data['searchByEmployee'] . "%");
            }
        }
        $adapter = new QueryAdapter($taskArray);
        $pagerfantaTaskArray = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            10
        );
        return $this->render("projectManager/taskOperations/all_task.html.twig", [
            'taskArray' => $pagerfantaTaskArray,
            'pagerfanta' => $pagerfantaTaskArray,
            'filterForm' => $form->createView()
        ]);
    }
}
