<?php

namespace App\Controller;

use App\Entity\OtpAuthentication;
use App\Entity\User;
use App\Form\Employee\RegistrationFormType;
use App\Form\OtpformType;
use App\Repository\OtpAuthenticationRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twilio\Rest\Client;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    // public function __construct(EmailVerifier $emailVerifier, EntityManagerInterface $entityManager)
    // {
    //     $this->emailVerifier = $emailVerifier;
    // }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, OtpAuthenticationRepository $otpAuthenticationRepository): Response
    {

        if ($this->isGranted("IS_AUTHENTICATED_FULLY")) {
            return $this->redirectToRoute("check_user_role");
        } 
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
           
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $phoneNumber = $form->getData()->getContactNumber();

            $entityManager->persist($user);

            $entityManager->flush();



            


            // // generate a signed url and email it to the user
            // $this->emailVerifier->   ('app_verify_email', $user,
            //     (new TemplatedEmail())
            //         ->from(new Address('aayushvyas06@gmail.com', 'Projecto'))
            //         ->to($user->getEmail())
            //         ->subject('Please Confirm your Email')
            //         ->htmlTemplate('registration/confirmation_email.html.twig')
            // );

            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_otp_verification', ['id' => $user->getId()]);

        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/otp-verification/{id}", name="app_otp_verification")
     */
    public function otpVerification(Request $request, OtpAuthenticationRepository $otpAuthenticationRepository, EntityManagerInterface $entityManager, UserRepository $userRespositry): Response
    {

        $userId = $request->get('id');
        $user = $userRespositry->find($userId);
        $otpAuthentication = $otpAuthenticationRepository->findOneBy(['User' => $userId], ['createdAt' => 'DESC']);
        $form = $this->createForm(OtpformType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $enteredOtp = $form->get('otp')->getData();

            if ($otpAuthentication->getOtp() === $enteredOtp) {
                $user->setIsVerified(true);
                $entityManager->persist($user);
                $entityManager->flush();
                          
                $this->addFlash('success', 'OTP Verified Successfully');
                return $this->redirectToRoute('app_login');


            } else {

                $this->addFlash('warning', 'Invalid OTP, please try again');
            }
        } else {

            $sid = "AC6f9936ba68699e39dc2c6b18b1223c69";
            $token = "d11a5375d7d85631733e04c95d175e83";
            $otp = mt_rand(100000, 999999);
            $user = $userRespositry->find($userId);
            $otpAuthentication = new OtpAuthentication();
            $otpAuthentication->setUser($user);
            $otpAuthentication->setOtp($otp);
            $otpAuthentication->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($otpAuthentication);
            $entityManager->flush();

            $client = new Client($sid,$token);
             $message = $client->messages->create("+919601587474", // to
                array(
                "from" => "+13159155826",
                "body" => "Dear ".$user->getFirstname()." your OTP for Projecto registration is ".$otp
                )
            );
            $contactNumber = $user->getContactNumber();
            $maskedNumber = str_pad(substr($contactNumber, -4), strlen($contactNumber), '*', STR_PAD_LEFT);
            $this->addFlash("success", "OTP sent Successfully, please enter the OTP which was sent to your ".$maskedNumber);
        }
        // Fetch the user ID from the route parameters





        if (!$otpAuthentication) {

        }


        return $this->render('otp/verify.html.twig', [
            'form' => $form->createView(),
            'userId' => $userId
        ]);
    }


    // #[Route('/verify/email', name: 'app_verify_email')]
    // public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    // {   

    //     // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    //     // validate email confirmation link, sets User::isVerified=true and persists

    //     $id = $request->get('id');
    //     try {
    //         $user = $userRepository->find($id);
    //         // dd($user);

    //         $this->emailVerifier->handleEmailConfirmation($request, $user);
    //     } catch (VerifyEmailExceptionInterface $exception) {
    //         dd("inside catch");
    //         $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
    //         return $this->redirectToRoute('app_register');
    //     }
    //     // $user->setIsVerified(true);

    //     // $entityManager->persist($user);
    //     // $entityManager->flush();
    //     // @TODO Change the redirect on success and handle or remove the flash message in your templates
    //     $this->addFlash('success', 'Your email address has been verified.');

    //     return $this->redirectToRoute('app_register');
    // }
}