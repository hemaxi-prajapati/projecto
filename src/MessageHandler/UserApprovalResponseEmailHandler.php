<?php


namespace App\MessageHandler;

use App\Entity\EmailRecords;
use App\Message\UserApprovalResponseEmail;
use App\Repository\ProjectDetailsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserApprovalResponseEmailHandler implements MessageHandlerInterface
{

  
  
    public function __construct(private MailerInterface $mailerInterface, private UserRepository $userRepository, private ProjectDetailsRepository $projectDetailsRepository, private EntityManagerInterface $entityManagerInterface)
    {
            
    }

    public function __invoke(UserApprovalResponseEmail $UserApprovalResponseEmail   )
    {
        $projectAssignment = $UserApprovalResponseEmail->getProjectAssignment();
        $projectAssignmentUser =  $this->userRepository->find($projectAssignment->getUser());
        $projectAssignmentProject = $this->projectDetailsRepository->find($projectAssignment->getProject());
        $projectAssignmentProjectManager = $this->userRepository->find($projectAssignmentProject->getProjectManager());
   
        $email = (new TemplatedEmail())
        ->from('projectonoreplay@example.com')
        ->to($projectAssignmentProjectManager->getEmail())
        ->subject('Projecto :  User '. $projectAssignment->getStatus() .'  for '. $projectAssignmentProject->getName())
        ->htmlTemplate('email/user_approval_Response_Email.html.twig')
        ->context([
            'projectAssignment' => $projectAssignment,
            'projectAssignmentUser' => $projectAssignmentUser,
            'projectAssignmentProject'=> $projectAssignmentProject,
            'projectAssignmentProjectManager'=> $projectAssignmentProjectManager
        ]);
         ;
         $emailRecord = new EmailRecords();
         try {
             $this->mailerInterface->send($email);
             $emailRecord->setStatus((EmailRecords::MAIL_STATUS_SENT));
         } catch (TransportExceptionInterface $e) {
             $emailRecord->setStatus((EmailRecords::MAIL_STATUS_FAIL));
         }
         $emailRecord->setToEmail($projectAssignmentProjectManager->getEmail());
         $emailRecord->setFromEmail('projectonoreplay@example.com');
         $emailRecord->setType('New user '.$projectAssignment->getStatus().' for project');
         $emailRecord->setCreatedAt(new \DateTimeImmutable());
         $this->entityManagerInterface->persist($emailRecord);
         $this->entityManagerInterface->flush();
       
    }
}   