<?php

namespace App\Controller;

use App\Controller\FileHandler\FileUploadHandler;
use App\Entity\DailyAttendance;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\Meetings;
use App\Entity\User;
use App\Form\ProfileDetailType;
use App\Form\FeedbackFormType;
use App\Form\GlobleTimer\DateFilterInGlobleTimer;
use App\Form\Meeting\CreateMeetingType;
use App\Repository\DailyAttendanceRepository;
use App\Repository\MeetingsRepository;
use App\Repository\UserProfilePhotoRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class PageManegerController extends AbstractController
{

    #[Route("/", name: "home_page")]
    public function homePage(Request $request, MailerInterface $mailer)
    {
        if ($this->isGranted("IS_AUTHENTICATED_FULLY")) {
            return $this->redirectToRoute("check_user_role");
        }
        $form = $this->createForm(FeedbackFormType::class);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted()) {
                $email = (new TemplatedEmail())
                    ->from($form['email']->getData())
                    ->to('projectonoreplay@example.com')
                    ->subject('Projecto :  Feedback Form Submitted')
                    ->htmlTemplate('email/feedbackform.html.twig')
                    ->context([
                        'Feedbackform' => $form->getData(),


                    ]);
                $mailer->send($email);

                // $this->addFlash('success', 'Feedback Form Submitted Successfully!');
            }
        } catch (Throwable $t) {
            // $this->addFlash('warning', 'Error updating user' . $t);
        }
        return $this->render("home.html.twig", ['feedbackForm' => $form->createView()]);
    }



    #[Route("/Profile", "profile_page")]
    public function profilePage(Request $request, FileUploadHandler $fileUploadHandler, UserProfilePhotoRepository $userProfilePhotoRepository, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $form = $this->createForm(ProfileDetailType::class, $user);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                if ($form['profileImage']->getData()) {
                    $newProfileImage = ($form['profileImage']->getData());
                    try {
                        $profileUploading = $fileUploadHandler->UpdateProfileImage($newProfileImage, $user);
                        $this->addFlash($profileUploading['status'] ? "success" : "warning", $profileUploading['message']);
                    } catch (Throwable $t) {
                        $this->addFlash("warning", "Opps!! : " . $t);
                    }
                }
                $this->addFlash('success', 'User Updated Successfully!');
                $entityManager->persist($user);
                $entityManager->flush();
            } else if ($form->isSubmitted()) {
                $this->addFlash('warning', 'Error While updating user ' . $form->getErrors());
            }
        } catch (Throwable $t) {
            $this->addFlash('warning', 'Error While updating user' . $t);
        }
        return $this->render("profile.html.twig", ['user' => $user, 'profileForm' => $form->createView()]);
    }

    #[Route("/getGlobleAttendanceTime", "get_globle_attendance")]
    public function getGlobleAttendanceTime(Request $request, DailyAttendanceRepository $dailyAttendanceRepository, EntityManagerInterface  $entityManager)
    {

        $userId = $this->getUser()->getId();
        $date = new \DateTime();
        $dateNow = $date->format('Y-m-d H:i:s');
        $todayAttendance = $dailyAttendanceRepository->findByUserWithCurruntdateQueryBuilder($userId, $dateNow)
            ->getQuery()
            ->getResult();
        $hour = 00;
        $minut = 00;
        $second = 00;

        foreach ($todayAttendance as $todayAttendanc) {
            $diff = ($todayAttendanc['checkOut']->diff($todayAttendanc['checkIn']));
            $second += $diff->format('%s');
            if ($second > 60) {
                $minut++;
                $second -= 60;
            }
            $minut += $diff->format('%i');
            if ($minut > 60) {
                $hour++;
                $minut -= 60;
            }
            $hour += $diff->format('%h');
        }

        $data = ['hour' => $hour, 'minut' => $minut, 'second' => $second];
        return new JsonResponse($data);
    }
    #[Route("/setCheckInGlobleAttendanceTime", "set_checkin_globle_attendance")]
    public function setCheckInGlobleAttendanceTime(Request $request, DailyAttendanceRepository $dailyAttendanceRepository, EntityManagerInterface  $entityManager)
    {
        $userId = $this->getUser()->getId();
        $dailyAttendance = new DailyAttendance();
        $dailyAttendance->setUser($this->getUser());
        $dailyAttendance->setCheckIn(new DateTime());
        $dailyAttendance->setCheckOut(new DateTime());
        $entityManager->persist($dailyAttendance);
        $entityManager->flush();


        return new JsonResponse(["responce" => "success"]);
    }
    #[Route("/setCheckOutGlobleAttendanceTime", "set_globle_attendance")]
    public function setCheckOutGlobleAttendanceTime(Request $request, DailyAttendanceRepository $dailyAttendanceRepository, EntityManagerInterface  $entityManager)
    {
        $date = new \DateTime();
        $dateNow = $date->format('Y-m-d H:i:s');
        $todayAttendance = $dailyAttendanceRepository->findByUserWithCurruntdateQueryBuilder($this->getUser()->getId(), $dateNow)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
        $todayAttendance = $dailyAttendanceRepository->find($todayAttendance[0]['id']);
        // dd($todayAttendance);
        $todayAttendance->setCheckOut(new \DateTime());
        $entityManager->persist($todayAttendance);
        $entityManager->flush();

        $data = ["return" => $request->request->get('hours')];

        return new JsonResponse($data);
    }
    #[Route("/showMyCheckinCheckOut", "show_my_checkin_check_out")]
    public function showMyCheckinCheckOut(Request $request, UserRepository $userRepository, DailyAttendanceRepository $dailyAttendanceRepository, EntityManagerInterface  $entityManager)
    {
        $allAttendance = $dailyAttendanceRepository->findAllTimeByUserQueryBuilder();
        $users = [];
        $page = $request->query->get('page', 1);
        $status = false;
        if (($this->getUser()->getMainRole() == User::ROLE_USER)) {
            $allAttendance = $allAttendance
                ->Where('d.user = :user')
                ->setParameter('user', $this->getUser()->getId());
            $users[$this->getUser()->getId()] = $this->getUser();
        } 
        // else if (($this->getUser()->getMainRole() == User::ROLE_TEAM_MANAGER)) {
        //     $allAttendance = $allAttendance
        //         ->Where('d.user = :user')
        //         ->setParameter('user', $this->getUser()->getId());
        // }
        $filterForm = $this->createForm(DateFilterInGlobleTimer::class);
        $filterForm->handleRequest($request);
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $allAttendance->andWhere("d.createdAt >= :from AND  d.createdAt <= :to")
                ->setParameter("from", $data['from']->format('y-m-d'))
                ->setParameter("to", date("y-m-d h:i:s", strtotime("+1 day", strtotime($data['to']->format('y-m-d')))));
        }

        // $allAttendance = $allAttendance->getQuery()
            // ->getResult();
        // dump($allAttendance->getQuery()->getResult());
        // $adapter = new QueryAdapter($allAttendance);
        $adapter = new ArrayAdapter($allAttendance->getQuery()->getResult());

        $allAttendance= Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            10
        );

        if (count($allAttendance) < 1) {
        } else {
            foreach ($allAttendance as $allAttendac) {
                $users[$allAttendac['userId']] = $userRepository->find($allAttendac['userId']);
            }
            $status = true;
        }
        return $this->render("check_in.html.twig", ['allAttendance' => $allAttendance, 'users' => $users, "status" => $status, "filterForm" => $filterForm]);
    }
    #[Route('/getLogsForDate', "logsForDate")]
    public function getLogsForDate(Request $request, DailyAttendanceRepository $dailyAttendanceRepository)
    {
        $date = $request->query->get('date');
        $userID = $request->query->get('user');
        $dateWithUserAttendance = $dailyAttendanceRepository->findByUserWithCurruntdateQueryBuilder($userID, $date)
            ->getQuery()
            ->getResult();
        $logsData = [];
        foreach ($dateWithUserAttendance as $log) {
            $logsData[] = [
                'checkIn' => $log['checkIn']->format('H:i:s'),
                'checkOut' => $log['checkOut']->format('H:i:s'),
                'TotalTime' => $log['totalTimeRow'],
            ];
        }
        return new JsonResponse(["logsForDate" => $logsData]);
    }
    #[Route('/Meetings/View', "meeting_view")]
    public function meetingView(Request $request, MeetingsRepository $meetingsRepository, DailyAttendanceRepository $dailyAttendanceRepository)
    {
        $page = $request->query->get('page', 1);
        $allMyMeeting = ($meetingsRepository->findByCreatedUserQueryBuilder($this->getUser()->getId()));
        $adapter = new QueryAdapter($allMyMeeting);
        $pagerfantaAllMyMeeting = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            10
        );
        return $this->render("meeting/view.html.twig", ['meetings' => $pagerfantaAllMyMeeting]);
    }
    #[Route('/Meetings/ViewSingleMeeting', "view_single_meeting")]
    public function ViewSingleMeeting(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManagerInterface, DailyAttendanceRepository $dailyAttendanceRepository, MeetingsRepository $meetingsRepository, HttpClientInterface $httpClient)
    {
        $meeting = $meetingsRepository->find($request->query->get('meetingId'));
        $form = $this->createForm(CreateMeetingType::class, $meeting);
        $form->handleRequest($request);
        return $this->render("meeting/view_single_meeting.html.twig", ['createMeetingForm' => $form->createView(), 'assignee' => $meeting->getMeetingAssignee()[0]]);
    }
    #[Route('/Meetings/Create', "create_meeting")]
    public function meetingCreate(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManagerInterface, DailyAttendanceRepository $dailyAttendanceRepository, MeetingsRepository $meetingsRepository, HttpClientInterface $httpClient)
    {
        $meeting = new Meetings();
        $form = $this->createForm(CreateMeetingType::class, $meeting);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $meetingAssignee = $form->get('meetingAssign')->getData();
            $assigneUserArray = [];
            $tempUser = $userRepository->find($meetingAssignee);
            $meeting->addMeetingAssignee($tempUser);
            $assigneUserArray['address'] = $tempUser->getEmail();
            $assigneUserArray['name'] = $tempUser->getName();
            $meeting->setCreatedBy($this->getUser());
            try {
                $data = [
                    'subject' => $form->get('subject')->getData(),
                    'start' => [
                        'dateTime' => $form->get('meetingStartTime')->getData()->format('Y-m-d H:I:s'),
                        'timeZone' => 'UTC'
                    ],
                    'end' => [
                        'dateTime' => $form->get('meetingEndTime')->getData()->format('Y-m-d H:I:s'),
                        'timeZone' => 'UTC'
                    ],
                    "attendees" => [
                        [
                            "emailAddress" =>
                            $assigneUserArray,
                            "type" => "required"
                        ]
                    ],
                    "isOnlineMeeting" => true,
                ];
                $event = (self::createEvent($httpClient, $this->getUser()->getPhotoSource()->getAccessToken(), $data));
                try {
                    $entityManagerInterface->persist($meeting);
                    $entityManagerInterface->flush();
                    $this->addFlash('success', "Meeting created");
                } catch (Throwable $t) {
                    $this->addFlash('warning', "Meeting Not created due to " . $t);
                }
            } catch (Throwable $t) {
                $this->addFlash('warning', "Meeting Not created due to " . $t);
            }
        }

        return $this->render("meeting/create.html.twig", ['createMeetingForm' => $form->createView()]);
    }
    private function createEvent($httpClient, $accessToken, $dataa)
    {
        $response = $httpClient->request('POST', 'https://graph.microsoft.com/v1.0/me/events', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'json' => $dataa
        ]);
        return ($response->getContent());
    }
}