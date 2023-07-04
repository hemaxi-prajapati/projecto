<?php

namespace App\Controller\EmployeeController;

use App\Repository\ProjectAssignmentRepository;
use App\Repository\ProjectDetailsRepository;
use App\Repository\TaskWithProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class EmployeeController extends AbstractController
{
    #[Route("/changeTaskStatus", "change_task_status")]
    public function changeTaskStatus(Request $request,  TaskWithProjectRepository $taskWithProjectRepository, EntityManagerInterface $entityManager)
    {
        if ($request->query->get("from") == "taskPage") {
            $TaskStatus = $request->query->get("status");
        } else
            $TaskStatus = $request->query->get("changedTasktype");

        $taskId = $request->query->get("id");
        $taskWithProject = $taskWithProjectRepository->find($taskId);
        $taskWithProject->setStatus($TaskStatus);
        $entityManager->flush();

        if ($request->query->get("from") == "taskPage") {
            $this->addFlash('success', $taskWithProject->getTitle() . " Task Status Updated To " . $taskWithProject->getStatus());
            return $this->redirectToRoute('app_employee_tasks');
        }
    }

    #[Route('/Employee/DashboardTaskView', "taskView")]
    public function taskView(UserRepository $userRepository, ProjectDetailsRepository $projectDetailsRepository, TaskWithProjectRepository $taskWithProjectRepository, Request $request)
    {
        $user = $this->getUser();
        $projectId = $request->query->get('id');
        $projectDetails = $projectDetailsRepository->find($projectId);
        $task = $taskWithProjectRepository->findTaskWithProjectAndUser($projectDetailsRepository->find($projectId), $user);
        return $this->render("employee/KanbanTask/index.html.twig", ['tasks' => $task, 'project' => $projectDetails]);
    }


    #[Route('/Employee/AssignedProject', "assignedProject")]
    public function assignedProject(ProjectAssignmentRepository $projectAssignmentRepository, Request $request)
    {
        $user = $this->getUser();
        $status = $request->query->get('status');
        $page = $request->query->get('page', 1);
        $assignedProjects = $status ? ($projectAssignmentRepository->findAssignedProjectsByStatus($user, $status)) : ($projectAssignmentRepository->findAssignedProject($user));
        $adapter = new QueryAdapter($assignedProjects);
        $pagerfantaAssignProject = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            10
        );

        $usersAssignedToProjects = [];
        $offset = 0;
        foreach ($assignedProjects->getQuery()->getResult() as $project) {
            $id = $project->getProject();
            $usersAssignedToProject = $projectAssignmentRepository->findUsersAssignedToProject($id);
            $usersAssignedToProjects[$offset++] = $usersAssignedToProject;
        }

        return $this->render('employee/assignedProject/assignedProject.html.twig', [
            'projects' => $pagerfantaAssignProject, 'usersAssignedToProjects' => $usersAssignedToProjects,
        ]);
    }
    #[Route('/Employee/Dashboard/tasks', "app_employee_tasks")]
    public function viewAllTaskForEmployee(TaskWithProjectRepository $taskWithProjectRepository, Request $request)
    {
        $page = $request->query->get('page', 1);
        $user = $this->getUser();
        $tasks = $taskWithProjectRepository->findAllTaskForEmployeeQueryBuilder($user);
        $tasks = $request->query->get("status") ? ($tasks->andWhere('task.Status = :status')
            ->setParameter('status', $request->query->get("status"))) : $tasks;


        $adapter = new QueryAdapter($tasks);
        $pagerfantaTaskArray = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            10
        );

        return $this->render("employee/AllTask/tasks.html.twig", [
            'tasks' => $pagerfantaTaskArray,
            'pagerFanta' => $pagerfantaTaskArray
        ]);
    }
    #[Route('/Employee/Dashboard/taskProgressChanger', "task_progress_changer")]
    public function taskProgressChanger(Request $request, EntityManagerInterface $entityManagerInterface, TaskWithProjectRepository $taskWithProjectRepository)
    {

        $taskId = $request->query->get('taskId');
        $progress = $request->query->get('progress');
        $task = $taskWithProjectRepository->find($taskId);
        $task->setProgress($progress);
        try {

            $entityManagerInterface->persist($task);
            $entityManagerInterface->flush();
            $this->addFlash("success", $task->getTitle() . " Task Progress Updated to " . $progress);
        } catch (Throwable $t) {
            $this->addFlash("warning", $task->getTitle() . " Task Progress Not Updated Got error : " . $t);
        }

        return $this->redirectToRoute("app_employee_tasks");
    }


    #[Route('/Employee/Dashboard/stopTimer', "app_task_timer")]
    public function stopTimerAction(Request $request, TaskWithProjectRepository $taskWithProjectRepository, EntityManagerInterface $entityManager)
    {
        $id = $request->query->get("taskId");
        $task = $taskWithProjectRepository->find($id);
        $time = $request->query->get("timerValue");
        $timeFromDB = date_format($task->getTimer(), "H:i:s");

        $timeInSeconds = strtotime($time) - strtotime('00:00:00');
        $timeFromDBInSeconds = strtotime($timeFromDB) - strtotime('00:00:00');
        $totalTimeInSeconds = $timeInSeconds + $timeFromDBInSeconds;
        $totalTime = gmdate('H:i:s', $totalTimeInSeconds);
        $task->setTimer(date_create_from_format('H:i:s', $totalTime));
        $entityManager->flush();
        return new JsonResponse(['timer' => $task->getTimer()->format('H:i:s')]);
    }
}
