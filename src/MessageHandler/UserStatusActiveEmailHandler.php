<?php


namespace App\MessageHandler;

use App\Entity\EmailRecords;
use App\Message\UserStatusActiveEmail;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserStatusActiveEmailHandler implements MessageHandlerInterface
{

  
  
    public function __construct(private MailerInterface $mailerInterface, private UserRepository $userRepository,private EntityManagerInterface $entityManagerInterface)
    {
            
    }

    public function __invoke(UserStatusActiveEmail $UserStatusActiveEmail)
    {
        $user = $UserStatusActiveEmail->getUser();
    
   
        $email = (new TemplatedEmail())
        ->from('projectonoreplay@example.com')
        ->to($user->getEmail())
        ->subject('Projecto :  User Activated')
        ->htmlTemplate('email/user_status_active.html.twig')
        ->context([
            'User' => $user,
        ]);
        ;
        $emailRecord = new EmailRecords();
        try {
            $this->mailerInterface->send($email);
            $emailRecord->setStatus((EmailRecords::MAIL_STATUS_SENT));
        } catch (TransportExceptionInterface $e) {
            $emailRecord->setStatus((EmailRecords::MAIL_STATUS_FAIL));
        }
        $emailRecord->setToEmail($user->getEmail());
        $emailRecord->setFromEmail('projectonoreplay@example.com');
        $emailRecord->setType('User Activated');
        $emailRecord->setCreatedAt(new \DateTimeImmutable());
        $this->entityManagerInterface->persist($emailRecord);
        $this->entityManagerInterface->flush();
       
    }
}   