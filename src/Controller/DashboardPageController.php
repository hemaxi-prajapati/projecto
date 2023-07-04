<?php

namespace App\Controller;

use App\Entity\ProjectDetails;
use App\Entity\TaskWithProject;
use App\Entity\User;
use App\Repository\DailyAttendanceRepository;

use App\Repository\ProjectAssignmentRepository;
use App\Repository\ProjectDetailsRepository;
use App\Repository\TaskWithProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;




class DashboardPageController extends AbstractController
{
    #[Route('/roleCheck', "check_user_role")]
    public function roleCheck(Security $security)
    {

        $user = $this->getUser();
        if (!$user->isIsVerified()) {
            $security->logout(false);
            return $this->redirectToRoute('app_otp_verification', ['id' => $user->getId()]);
        }
        if (($user->getDepartment() == null)) {
            $this->addFlash("warning", "!! You Haven't Assign Any Department Yet Ask Your Team Manager To Assign Department ");
            return $this->redirectToRoute("employee_dashboard");
        }
        if (in_array(User::ROLE_TEAM_MANAGER, $user->getRoles())) {
            return $this->redirectToRoute("team_manager_dashboard");
        } elseif (in_array(User::ROLE_PROJECT_MANAGER, $user->getRoles())) {
            return $this->redirectToRoute("project_manager_dashboard");
        } elseif (in_array(User::ROLE_USER, $user->getRoles())) {
            return $this->redirectToRoute("employee_dashboard");
        }
    }
    #[Route('/ProjectManager/Dashboard', "project_manager_dashboard")]
    public function projectManagerDashboard(ProjectDetailsRepository $projectDetailsRepository, ChartBuilderInterface $chartBuilder, TaskWithProjectRepository $taskWithProjectRepository)
    {

        $projectsCount = $projectDetailsRepository->findAllProjectsWithCountStatusQueryBuilder($this->getUser())->getQuery()->getResult();
        $countAllProject = [];
        $totalProject = 0;
        foreach ($projectsCount as $project) {
            $countAllProject[$project['Status']] = $project['1'];
            $totalProject += $project['1'];
        }
        $allProjectsCount = $totalProject;
        $openProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_OPEN]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_OPEN] : 0;
        $inProgressProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_IN_PROGRESS]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_IN_PROGRESS] : 0;
        $onHoldProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_ON_HOLD]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_ON_HOLD] : 0;
        $completedProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_COMPLETED]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_COMPLETED] : 0;

        $chartProject = $chartBuilder->createChart(Chart::TYPE_PIE);

        $chartProject->setData([
            'labels' => [ProjectDetails::PROJECT_STATUS_IN_PROGRESS, ProjectDetails::PROJECT_STATUS_OPEN, ProjectDetails::PROJECT_STATUS_ON_HOLD, ProjectDetails::PROJECT_STATUS_COMPLETED],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => ['#dc3545', '#6c757d', '#ffc107', '#198754'],
                    'data' => [$inProgressProjectsCount, $openProjectsCount, $onHoldProjectsCount, $completedProjectsCount],
                ],

            ],
        ]);

        $tasksCount = $taskWithProjectRepository->findAllTaskForProjectManagerWithStatusCountQueryBuilder($this->getUser())->getQuery()
            ->getResult();
        $countAll = [];
        $totalTask = 0;
        foreach ($tasksCount as $task) {
            $countAll[$task['Status']] = $task['1'];
            $totalTask += $task['1'];
        }


        $allTaskCount = $totalTask;
        $openTaskCount = isset($countAll[TaskWithProject::TASK_STATUS_OPEN]) ? $countAll[TaskWithProject::TASK_STATUS_OPEN] : 0;
        $onHoldTaskCount = isset($countAll[TaskWithProject::TASK_STATUS_ON_HOLD]) ? $countAll[TaskWithProject::TASK_STATUS_ON_HOLD] : 0;
        $inProgressTaskCount = isset($countAll[TaskWithProject::TASK_STATUS_IN_PROGRESS]) ? $countAll[TaskWithProject::TASK_STATUS_IN_PROGRESS] : 0;
        $completedTaskCount = isset($countAll[TaskWithProject::TASK_STATUS_COMPLETED]) ? $countAll[TaskWithProject::TASK_STATUS_COMPLETED] : 0;

        $chartTask = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chartTask->setData([
            'labels' => ['In Progress', 'On Hold', 'Open', 'Completed'],
            'datasets' => [
                [
                    'label' => 'Tasks Status',
                    'backgroundColor' => ['#dc3545', '#ffc107', '#6c757d', '#198754'],
                    'data' => [$inProgressTaskCount, $onHoldTaskCount, $openTaskCount, $completedTaskCount],
                ],
            ],
        ]);


        return $this->render(
            "projectManager/index.html.twig",
            [
                'allProjectsCount' => $allProjectsCount,
                'inProgressProjectsCount' => $inProgressProjectsCount,
                'onHoldProjectsCount' => $onHoldProjectsCount,
                'openProjectsCount' => $openProjectsCount,
                'completedProjectsCount' => $completedProjectsCount,

                'chartProject' => $chartProject,

                'allTaskCount' => $allTaskCount,
                'inProgressTaskCount' => $inProgressTaskCount,
                'onHoldTaskCount' => $onHoldTaskCount,
                'openTaskCount' => $openTaskCount,
                'completedTaskCount' => $completedTaskCount,

                'chartTask' => $chartTask,
            ]
        );
    }
    #[Route('/TeamManager/Dashboard', "team_manager_dashboard")]
    public function teamManagerDashboard(ProjectAssignmentRepository $projectAssignmentRepository, ProjectDetailsRepository $projectDetailsRepository, ChartBuilderInterface $chartBuilderInterface, UserRepository $userRepository)
    {

        $projectsCount = $projectDetailsRepository->findAllProjectsWithCountStatusWithDepatmentQueryBuilder($this->getUser()->getDepartment()->getId())->getQuery()->getResult();
        $countAllProject = [];
        $totalProject = 0;
        foreach ($projectsCount as $project) {
            $countAllProject[$project['Status']] = $project['1'];
            $totalProject += $project['1'];
        }

        $allProjectsCount = $totalProject;
        $openProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_OPEN]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_OPEN] : 0;
        $inProgressProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_IN_PROGRESS]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_IN_PROGRESS] : 0;
        $onHoldProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_ON_HOLD]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_ON_HOLD] : 0;
        $completedProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_COMPLETED]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_COMPLETED] : 0;

        $chartProject = $chartBuilderInterface->createChart(Chart::TYPE_PIE);

        $chartProject->setData([
            'labels' => [ProjectDetails::PROJECT_STATUS_IN_PROGRESS, ProjectDetails::PROJECT_STATUS_OPEN, ProjectDetails::PROJECT_STATUS_ON_HOLD, ProjectDetails::PROJECT_STATUS_COMPLETED],
            'datasets' => [
                [
                    'label' => 'Project By Status',
                    'backgroundColor' => ['#dc3545', '#6c757d', '#ffc107', '#198754'],
                    'data' => [$inProgressProjectsCount, $openProjectsCount, $onHoldProjectsCount, $completedProjectsCount],
                ],

            ],
        ]);

        $employeesCount = $userRepository->findAllRoleEmployeeWithCountStatusQueryBuilder()->getQuery()->getResult();
        $countAllUser = [];
        $totalUser = 0;
        foreach ($employeesCount as $employee) {
            $countAllUser[$employee['status']] = $employee['1'];
            $totalUser += $employee['1'];
        }
        $activeEmployee =  $countAllUser[User::USER_STATUS_ACTIIVE];
        $inactiveEmployee = $countAllUser[User::USER_STATUS_INACTIIVE];
        $chartEmployee = $chartBuilderInterface->createChart(Chart::TYPE_PIE);

        $chartEmployee->setData([
            'labels' => [User::USER_STATUS_INACTIIVE, User::USER_STATUS_ACTIIVE],
            'datasets' => [
                [
                    'label' => 'Active InActive Employee',
                    'backgroundColor' => ['#dc3545', '#198754'],
                    'data' => [$inactiveEmployee, $activeEmployee],
                ],

            ],
        ]);

        return $this->render(
            "teamManager/index.html.twig",
            [
                'onHoldProjectsCount' => $onHoldProjectsCount,
                'inProgressProjectsCount' => $inProgressProjectsCount,
                'allProjectsCount' => $allProjectsCount,
                'openProjectsCount' => $openProjectsCount,
                'completedProjectsCount' => $completedProjectsCount,
                'chartProject' => $chartProject,
                'chartEmployee' => $chartEmployee
            ]
        );
    }


    #[Route('/Employee/Dashboard', "employee_dashboard")]
    public function employeeDashboard(ChartBuilderInterface $chartBuilderInterface, ProjectAssignmentRepository $projectAssignmentRepository, TaskWithProjectRepository $taskWithProjectRepository, DailyAttendanceRepository $dailyAttendanceRepository,)
    {
        $user = $this->getUser();
        $projectsCount = $projectAssignmentRepository->findProjectAssignUserWithCountStatusQuerybuilder($user)->getQuery()->getResult();
        $countAllProject = [];
        foreach ($projectsCount as $project) {
            $countAllProject[$project['Status']] = $project['1'];
        }

        $openProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_OPEN]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_OPEN] : 0;
        $inProgressProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_IN_PROGRESS]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_IN_PROGRESS] : 0;
        $onHoldProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_ON_HOLD]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_ON_HOLD] : 0;
        $completedProjectsCount = isset($countAllProject[ProjectDetails::PROJECT_STATUS_COMPLETED]) ? $countAllProject[ProjectDetails::PROJECT_STATUS_COMPLETED] : 0;
        $chartProject = $chartBuilderInterface->createChart(Chart::TYPE_PIE);

        $chartProject->setData([
            'labels' => [ProjectDetails::PROJECT_STATUS_IN_PROGRESS, ProjectDetails::PROJECT_STATUS_OPEN, ProjectDetails::PROJECT_STATUS_ON_HOLD, ProjectDetails::PROJECT_STATUS_COMPLETED],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => ['#dc3545', '#6c757d', '#ffc107', '#198754'],
                    'data' => [$inProgressProjectsCount, $openProjectsCount, $onHoldProjectsCount, $completedProjectsCount],
                ],

            ],
        ]);
        $tasksCount = [];
        $tasksCount = $taskWithProjectRepository->findAllTaskForUserWithStatusCountQueryBuilder($this->getUser())->getQuery()->getResult();

        $countAll = [];
        $totalTask = 0;
        foreach ($tasksCount as $task) {
            $countAll[$task['Status']] = $task['1'];
            $totalTask += $task['1'];
        }


        $allTaskCount = $totalTask;
        $openTaskCount = isset($countAll[TaskWithProject::TASK_STATUS_OPEN]) ? $countAll[TaskWithProject::TASK_STATUS_OPEN] : 0;
        $onHoldTaskCount = isset($countAll[TaskWithProject::TASK_STATUS_ON_HOLD]) ? $countAll[TaskWithProject::TASK_STATUS_ON_HOLD] : 0;
        $inProgressTaskCount = isset($countAll[TaskWithProject::TASK_STATUS_IN_PROGRESS]) ? $countAll[TaskWithProject::TASK_STATUS_IN_PROGRESS] : 0;
        $completedTaskCount = isset($countAll[TaskWithProject::TASK_STATUS_COMPLETED]) ? $countAll[TaskWithProject::TASK_STATUS_COMPLETED] : 0;


        $chartTask = $chartBuilderInterface->createChart(Chart::TYPE_PIE);
        $chartTask->setData([
            'labels' => ['In Progress', 'On Hold', 'Open', 'Completed'],
            'datasets' => [
                [
                    'label' => 'Tasks Status',
                    'backgroundColor' => ['#dc3545', '#ffc107', '#6c757d', '#198754'],
                    'data' => [$inProgressTaskCount, $onHoldTaskCount, $openTaskCount, $completedTaskCount],
                ],
            ],
        ]);

        $userId = $this->getUser()->getId();
        $lastSevenDaysAttendance = $dailyAttendanceRepository->findByUserWithLastSevenDaaysTotalTime($userId)
            ->getQuery()
            ->getResult();
        $totalLoggedInTimePerDay = [];


        foreach ($lastSevenDaysAttendance as $attendance) {
            $createdAt = $attendance->getCreatedAt()->format('Y-m-d');
            $checkIn = $attendance->getCheckIn();
            $checkOut = $attendance->getCheckOut();

            $loggedInTime = $checkOut->getTimestamp() - $checkIn->getTimestamp();

            if (!isset($totalLoggedInTimePerDay[$createdAt])) {
                $totalLoggedInTimePerDay[$createdAt] = $loggedInTime;
            } else {
                $totalLoggedInTimePerDay[$createdAt] += $loggedInTime;
            }
        }

        // Convert total logged-in time to formatted hours:minutes:seconds representation
        foreach ($totalLoggedInTimePerDay as $date => $totalSeconds) {
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            $totalLoggedInTimePerDay[$date] = $formattedTime;
        }
        $data = [];
        foreach ($totalLoggedInTimePerDay as $formattedTime) {
            $timeParts = explode(':', $formattedTime);
            $hours = (int) $timeParts[0];
            $data[] = $hours;
        }

        $chart = $chartBuilderInterface->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => array_keys($totalLoggedInTimePerDay), // Use the dates from $totalLoggedInTimePerDay
            'datasets' => [
                [
                    'label' => 'Hours worked 👩‍💻',
                    'backgroundColor' =>
                    [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)',
                        'rgb(56, 203, 186)',


                    ],
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $data, // Use the formatted hours worked
                    'tension' => 0.4,
                ],
            ],
        ]);
        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 16,
                ],
            ],
        ]);



        return $this->render("employee/index.html.twig", [
            'chartProject' => $chartProject,
            'allTaskCount' => $allTaskCount,
            'openTaskCount' => $openTaskCount,
            'onHoldTaskCount' => $onHoldTaskCount,
            'inProgressTaskCount' => $inProgressTaskCount,
            'completedTaskCount' => $completedTaskCount,
            'chartTask' => $chartTask,
            'chart' => $chart

        ]);
    }
}
