<?php

namespace App\Controller\ProjectManagerController\ProjectController\TaskController;

use App\Form\Task\CreateTaskType;
use App\Entity\TaskWithProject;
use App\Entity\User;
use App\Message\CreateTaskEmail;
use App\Repository\ProjectAssignmentRepository;
use App\Repository\ProjectDetailsRepository;
use App\Repository\TaskWithProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;


class CreateTaskController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    //create task page 

    #[Route('/ProjectManager/Dashboard/Projects/Tasks/Create', name: 'create_task')]
    public function createTask(Request $request, ProjectDetailsRepository $projectDetailsRepository, EntityManagerInterface $entityManager, UserRepository $userRepository, ProjectAssignmentRepository $projectAssignmentRepository, MessageBusInterface $messageBusInterface): Response
    {
        $pid = $request->query->get("id");
        $project = $projectDetailsRepository->find($pid);

        $task = new TaskWithProject();
        $createTaskForm = $this->createForm(CreateTaskType::class, $task);
        $createTaskForm->handleRequest($request);

        $userAssignTaskarray = $userRepository->findAllUserToAssignTask($pid);
        if (count($userAssignTaskarray) == 0) {
            $this->addFlash('warning', 'You Need Atleast One Approved User. Then You can Create Task ');
            return $this->redirectToRoute('manage_task', [
                'id' => $pid
            ]);
        }
        $userInTaskOld = [];
        foreach ($task->getUsers() as $user) {
            $userInTaskOld[$user->getId()] = ($user->getId());
        }
        if ($createTaskForm->isSubmitted() && $createTaskForm->isValid()) {

            $userAssignIds = $request->request->all();
            $userAssignIds = $userAssignIds['assignedTo'];
            foreach ($userInTaskOld as $oldUser) {
                $oldUser = $userRepository->find($oldUser);
                $task->removeUser($oldUser);
            }
            $assignedUser = null;
            foreach ($userAssignIds as $userAssignId) {
                $assignedUser = $userRepository->find($userAssignId);
                $task->addUser($assignedUser);
            }
            $task->setProject($project);

            try {
                $entityManager->persist($task);
                $entityManager->flush();
                $this->addFlash('success', 'New task Have been assigned to  ' . $assignedUser->getFirstname());
            } catch (Throwable $t) {
                $this->addFlash('warning', 'Opps Some Error Occurs :' . $t);
            }

            $messageBusInterface->dispatch(new CreateTaskEmail($task, $assignedUser));

            return $this->redirectToRoute('manage_task', [
                'id' => $pid
            ]);
        }
        return $this->render('projectManager/taskOperations/create_task.html.twig', [
            'createTaskForm' => $createTaskForm->createView(),
            'userAssignTaskarray' => $userAssignTaskarray,
            'project' => $project,
            'users' => $userInTaskOld
        ]);
    }


    //View task page 

    #[Route('/Tasks/View', name: 'view_task')]
    public function editTask(Request $request, ProjectDetailsRepository $projectDetailsRepository, EntityManagerInterface $entityManager, UserRepository $userRepository, ProjectAssignmentRepository $projectAssignmentRepository, TaskWithProjectRepository $taskWithProjectRepository): Response
    {
        $pid = $request->query->get("id");
        $tid = $request->query->get("tid");
        $project = null;
        if ($this->getUser()->getMainRole() == User::ROLE_TEAM_MANAGER) {
            $projectsIdArray = $projectAssignmentRepository->findProjectByDepartmentQuerybuilder($this->getUser()->getDepartment()->getId())->getQuery()->getResult();
            $isTrueProject = false;
            foreach ($projectsIdArray as $projectId) {
                if ($projectId['projectId'] == $request->query->get('id')) {
                    $isTrueProject = true;
                    break;
                }
            }
            if ($isTrueProject)
                $project = [1];
        } else if ($this->getUser()->getMainRole() == User::ROLE_PROJECT_MANAGER) {
            $project = $projectDetailsRepository->findAllProjectsByAnyTypeUserQueryBuilder($this->getUser(), $pid)
                ->getQuery()
                ->getResult();
        } else {
            $tasks = $taskWithProjectRepository->findAllTaskForEmployeeQueryBuilder($this->getUser()->getId())
                ->andWhere('task.id = :Tid')
                ->setParameter('Tid', $tid)
                ->getQuery()
                ->getResult();
            if ($tasks) {
                $project = [1];
            }
        }
        $task = $taskWithProjectRepository->find($tid);
        if ($project and $task) {
            ($project = $project[0]);

            // $task = $taskWithProjectRepository->find($tid);
            $viewTaskForm = $this->createForm(CreateTaskType::class, $task);
            $viewTaskForm->handleRequest($request);

            //get all users approved to task assign

            $userAssignTaskarray = $userRepository->findAllUserToAssignTask($pid);

            //get user perviously assigned to task for edited selection

            $userInTaskOld = [];
            foreach ($task->getUsers() as $user) {
                $userInTaskOld[$user->getId()] = ($user->getId());
            }
            if ($viewTaskForm->isSubmitted() && $viewTaskForm->isValid()) {
                //get updated assigned user 
                $allPostData = ($request->request->all());
                $userAssignIds = $allPostData['assignedTo'];
                foreach ($userInTaskOld as $oldUser) {
                    $oldUser = $userRepository->find($oldUser);
                    $task->removeUser($oldUser);
                }
                $assignedUser = null;
                foreach ($userAssignIds as $userAssignId) {
                    $assignedUser = $userRepository->find($userAssignId);
                    $task->addUser($assignedUser);
                }
                try {
                    $entityManager->persist($task);
                    $entityManager->flush();
                } catch (Throwable $t) {
                    $this->addFlash('warning', 'Opps Some Error Occurs :' . $t);
                }

                $this->addFlash('success', 'Task updated Successfully');
                // return $this->redirect($request->server->get('HTTP_REFERER'));
                return $this->redirectToRoute("check_user_role");
            }
            return $this->render('projectManager/taskOperations/create_task.html.twig', [
                'createTaskForm' => $viewTaskForm->createView(),
                'userAssignTaskarray' => $userAssignTaskarray,
                'project' => $project,
                'users' => $userInTaskOld
            ]);
        } else {
            $this->addFlash("warning", "You are Not Allow to See Other's Task  ");
            return $this->redirectToRoute("check_user_role");
        }
    }
}
