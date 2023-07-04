<?php

namespace App\Controller\TeamManegerController;

use App\Entity\Trasaction;
use App\Entity\User;
use App\Repository\TrasactionRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Employee\FilterEmployeeType as EmployeeFilterEmployeeType;
use Stripe;
use Throwable;

class PaymentController extends AbstractController
{

  #[Route('/payload', name: 'payload')]

  public function payload(Request $request, UserRepository $userRepository)
  {
    $page = $request->query->get('page', 1);
    $activeusersQueryBuilder = $userRepository->findAllRoleEmployeeQueryBuilder()
      ->andWhere('u.status = :status')
      ->setParameter('status', User::USER_STATUS_ACTIIVE);

    $form = $this->createForm(EmployeeFilterEmployeeType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();
      if ($data['value']) {
        if ($data['searchBy'] == 'name') {
          $activeusersQueryBuilder->andWhere("u.Firstname LIKE :name ")
            ->setParameter("name", '%' . $data['value'] . '%');
        } else {
          $activeusersQueryBuilder->andWhere("u.email LIKE :email ")
            ->setParameter("email", '%' . $data['value'] . '%');
        }
      }
      // $page = 1;
    }
    $adapter = new QueryAdapter($activeusersQueryBuilder);
    $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage(
      $adapter,
      $page,
      10
    );
    return $this->render("teamManager/payment/payload.html.twig", [
      'users' => $pagerfanta,
      'filterForm' => $form->createView()

    ]);
  }

  #[Route('/stripe', name: 'stripe', methods: ['POST'])]
  public function payment(Request $request): Response
  {

    $amount = $request->request->get('amount');
    return $this->render("teamManager/payment/payment.html.twig", [
      'amount' => $amount
    ]);
  }


  #[Route('/stripepost', name: 'stripepost', methods: ['POST'])]
  public function stripepost(Request $request)
  {
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET']);

    try {
      $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET']);

      //retrive amount
      $jsonObj = json_decode(file_get_contents('php://input'));
      $amount = $jsonObj->amountpost[0]->amount;
      $amountString = trim((string) $amount);


      // Create a PaymentIntent with amount and currency
      $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amountString * 100,
        'currency' => 'inr',
        'customer' => 'cus_O1eQ0Iiymva84b',
        'automatic_payment_methods' => [
          'enabled' => true,
        ],
      ]);

      $output = [
        'clientSecret' => $paymentIntent->client_secret,
      ];
    } catch (Error $e) {
      http_response_code(500);
      dump($e->getMessage());
    }

    return new JsonResponse($output);
  }




  #[Route('/stripesuccess', name: 'stripesuccess')]
  public function stripesuccess(Request $request, EntityManagerInterface $entityManagerInterface, Security $security)
  {
    $user = $this->getUser();
    $security->login($user);
    $this->addFlash('success', 'your payment has completed successfully');
    return $this->redirectToRoute('home_page');
  }



  #[Route('/webhook', name: 'webhook')]
  public function Webhook(Request $request, EntityManagerInterface $entityManagerInterface, TrasactionRepository $trasactionRepository)
  {
    $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET']);

    $endpoint_secret = 'whsec_NFAHI5ZXb6DGRHskmCV1OhpeUYqV5Lyf';


    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $event = null;

    try {
      $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sig_header,
        $endpoint_secret
      );
    } catch (\UnexpectedValueException $e) {
      // Invalid payload
      http_response_code(400);
      exit();
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
      // Invalid signature
      http_response_code(400);
      exit();
    }

    // Handle the event
    $paymentIntent = $event->data->object;

    if ($event->type == 'payment_intent.created') {
      try {
        $trasaction = new Trasaction;
      } catch (Throwable $t) {
        http_response_code(400);
        return new Response($t);
      }
    } else {
      try {
        $trasaction = $trasactionRepository->findOneBy(['paymentId' => $paymentIntent['id']]);
      } catch (Throwable $t) {
        http_response_code(400);
        return new Response($t);
      }
    }

    $trasaction->setPaymentId($paymentIntent['id']);
    $trasaction->setStatus($paymentIntent['status']);
    $trasaction->setAmount($paymentIntent['amount']);
    $trasaction->setReceivedAmount($paymentIntent['amount_received']);
    $trasaction->setCreatedTimestamp($paymentIntent['created']);
    $trasaction->setCustomerId($paymentIntent['customer']);
    $trasaction->setPaymentMethod($paymentIntent['payment_method_types'][0]);
    $trasaction->setUpdatedAt(new DateTime());

    try {

      $entityManagerInterface->persist($trasaction);
      $entityManagerInterface->flush();
    } catch (Throwable $t) {
      http_response_code(400);
      return new Response($t);
    }

    http_response_code(200);
    return new Response(" database entry done ");
  }



  #[Route('/transactions', name: 'transactions')]

  public function gettransactions(TrasactionRepository $trasactionRepository)
  {

    $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET']);

    $paymentIntents = $trasactionRepository->findAll();



    return $this->render("teamManager/payment/transactions.html.twig", [
      'paymentIntents' => $paymentIntents,
    ]);
  }
}
