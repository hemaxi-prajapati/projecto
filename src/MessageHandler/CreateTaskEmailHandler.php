<?php


namespace App\MessageHandler;

use App\Entity\EmailRecords;
use App\Message\CreateTaskEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateTaskEmailHandler implements MessageHandlerInterface
{
    public function __construct(private MailerInterface $mailerInterface, private EntityManagerInterface $entityManagerInterface)
    {
    }
    public function __invoke(CreateTaskEmail $createTaskEmail)
    {
        $task = $createTaskEmail->getTaskWithProject();
        $taskTitle = $task->getTitle();
        $email = (new TemplatedEmail())
            ->from('projectonoreplay@example.com')
            ->to($createTaskEmail->getuser()->getEmail())
            ->subject('Projecto :  New Task have been Assigned')
            ->htmlTemplate('email/new_task_assigned_email.html.twig')
            ->context([
                'user' => $createTaskEmail->getuser(),
                'task' => $task
            ]);;
        $emailRecord = new EmailRecords();
        try {
            $this->mailerInterface->send($email);
            $emailRecord->setStatus((EmailRecords::MAIL_STATUS_SENT));
        } catch (TransportExceptionInterface $e) {
            $emailRecord->setStatus((EmailRecords::MAIL_STATUS_FAIL));
        }
        $emailRecord->setToEmail($createTaskEmail->getuser()->getEmail());
        $emailRecord->setFromEmail('projectonoreplay@example.com');
        $emailRecord->setType('New task assign to user');
        $emailRecord->setCreatedAt(new \DateTimeImmutable());
        $this->entityManagerInterface->persist($emailRecord);
        $this->entityManagerInterface->flush();
    }
}
