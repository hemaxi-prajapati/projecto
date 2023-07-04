<?php



namespace App\Controller\ProjectManagerController\ProjectTeamController;

use App\Entity\ProjectAssignment;
use App\Entity\ProjectDetails;
use App\Repository\ProjectDetailsRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddTeamMemberController extends AbstractController
{


    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }




    #[Route('/ProjectManager/Dashboard/Projects/Team', name: 'project_add_team_member')]
    public function createTask(Request $request, ProjectDetailsRepository $projectDetailsRepository, UserRepository $userRepository): Response
    {
        $projectID = $request->query->get('id');
        $userNotInThisProject = $userRepository->findAllNotInThisProjectQueryBuilder($projectID);
        $adapter=new QueryAdapter($userNotInThisProject);
        $users=Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page',1),
            10
        );



        return $this->render('projectManager/teamOperations/add_team_member.html.twig', ['users' => $users, 'projectId' => $projectID]);
    }

    #[Route('/ProjectManager/Dashboard/Projects/Team/RequestToTeamManeger', name: 'request_user_for_project')]
    public function requestUserForProject(Request $request, UserRepository $userRepository, ProjectDetailsRepository $projectDetailsRepository,EntityManagerInterface $entityManagerInterface): Response
    {
        $projectId = $request->query->get('projectId');
        $userId = $request->query->get('userId');
        $projectAssign = new ProjectAssignment();
        $projectAssign->setProject($projectDetailsRepository->find($projectId));
        $projectAssign->setUser($userRepository->find($userId));
        $projectAssign->setStatus(ProjectAssignment::USER_TASK_STATUS_YET_TO_ASSIGN);
        $projectAssign->setAssignAt(new DateTimeImmutable());
        $entityManagerInterface->persist($projectAssign);
        $entityManagerInterface->flush();

        $this->addFlash('success', 'Sent Request Success');

        return $this->redirectToRoute("project_add_team_member", ["id" => $projectId]);
    }
}