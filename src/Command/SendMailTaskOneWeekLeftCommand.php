<?php

namespace App\Command;

use App\Entity\EmailRecords;
use App\Repository\TaskWithProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:send-mail-task-one-week-left',
    description: 'send mail to user before one week left to project',
)]
class SendMailTaskOneWeekLeftCommand extends Command
{
    private $taskWithProjectRepository;
    private $mailerInterface;
    private $entityManagerInterface;
    public function __construct(TaskWithProjectRepository $taskWithProjectRepository, MailerInterface $mailerInterface,EntityManagerInterface $entityManagerInterface)
    {
        parent::__construct(null);
        $this->taskWithProjectRepository = $taskWithProjectRepository;
        $this->mailerInterface = $mailerInterface;
        $this->entityManagerInterface= $entityManagerInterface;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tasks = $this->taskWithProjectRepository->findByNowDate();
        $io->progressStart(count($tasks));
        foreach ($tasks as $task) {
            $io->progressAdvance();
            $email = (new TemplatedEmail())
                ->from('projectonoreplay@example.com')
                ->to($task->getProjectAssignment()->getUser()->getEmail())
                ->subject('Projecto :  Remiender For Task')
                ->htmlTemplate('email/task_near_to_deadline_email.html.twig')
                ->context([
                    'user' => $task->getProjectAssignment()->getUser(),
                    'task' => $task
                ]);
            $emailRecord =new EmailRecords();

            try {
                $this->mailerInterface->send($email);
                $emailRecord->setStatus((EmailRecords::MAIL_STATUS_SENT));
            } catch (TransportExceptionInterface $e) {
                $emailRecord->setStatus((EmailRecords::MAIL_STATUS_FAIL));
            }

            $emailRecord->setToEmail($task->getProjectAssignment()->getUser()->getEmail());
            $emailRecord->setFromEmail('projectonoreplay@example.com');
            $emailRecord->setType('One week left for task');
            $emailRecord->setCreatedAt(new \DateTimeImmutable());
            $this->entityManagerInterface->persist($emailRecord);
            $this->entityManagerInterface->flush();
        }
        $io->progressFinish();
        $io->success('Send Mail To All User Success');

        return Command::SUCCESS;
    }
}
