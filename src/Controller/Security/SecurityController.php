<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Entity\UserProfilePhoto;
use App\Repository\UserProfilePhotoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use League\OAuth2\Client\Provider\Google;

class SecurityController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }


    #[Route('/login/google-login-completed', name: 'google_login_completed')]
    public function GoogleLoginCompleted(Request $request, EntityManagerInterface $entityManagerInterface, UserRepository $userRepository, Security $security, UserProfilePhotoRepository $userProfilePhotoRepository): RedirectResponse
    {
        $code = $request->query->get('code');
        if (!$code) {
            $this->addFlash("warning", "Opps!! Authentication Failed..");
            // throw new AuthenticationException('Authentication failed.');
            return $this->redirectToRoute('app_login');
        }
        $redirectUri = $this->generateUrl('google_login_completed', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $provider = new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRATE'],
            'redirectUri' => $redirectUri
        ]);
        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            $this->addFlash("warning", 'Opps !! Error : ' . $e);
            return $this->redirectToRoute('app_login');
        }
        $userData = $provider->getResourceOwner($accessToken);
        $userMail = $userData->getEmail();
        $user = $userRepository->findByEmail($userMail);
        if ($user) {
            $user = $user[0];
            if ($user->getLoginFrom() != User::USER_LOGIN_FROM_GOOGLE) {
                $this->addFlash("warning", "Opps !! Previously From this Mail You Loged in Via :" . $user->getLoginFrom() . " So Please Logia Via Their Only");
                return $this->redirectToRoute('app_login');
            }
            $source = $userProfilePhotoRepository->findby(['user' => $user]);
            if (!$source) {
                $source = [new UserProfilePhoto()];
            }
            $source[0]->setUser($user);
            $source[0]->setSource($userData->getAvatar());
            $source[0]->setAccessToken($accessToken);
            $entityManagerInterface->persist($source[0]);
            $entityManagerInterface->flush();
        } else {
            $user = new User();
            $user->setEmail($userMail);
            $user->setFirstname($userData->getFirstName());
            $user->setLastName($userData->getLastName());
            $user->setStatus(User::USER_STATUS_ACTIIVE);
            $user->setRoles([User::ROLE_USER]);
            $user->setIsVerified(true);
            $user->setLoginFrom(user::USER_LOGIN_FROM_GOOGLE);
            $user->setPassword("User Login from " . $user->getLoginFrom());
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            $source = new UserProfilePhoto();
            $source->setAccessToken($accessToken);
            $source->setUser($user);
            $source->setSource($userData->getAvatar());
            try {
                $entityManagerInterface->persist($source);
                $entityManagerInterface->flush();
            } catch (Throwable $t) {
                return new CustomUserMessageAuthenticationException("opps: " . $t);
            }
            $this->addFlash("success", "Welcome to Projecto");
        }
        $security->login($user);
        return $this->redirectToRoute('check_user_role');
    }
    
    #[Route('/login/microsoft-login-completed', name: 'microsoft_login_completed')]
    public function microsoftLoginCompleted(Request $request, EntityManagerInterface $entityManagerInterface, UserRepository $userRepository, Security $security, UserProfilePhotoRepository $userProfilePhotoRepository): RedirectResponse
    {
        $code = $request->query->get('code');
        if (!$code) {
            $this->addFlash("warning", "Opps!! Authentication Failed..");
            // throw new AuthenticationException('Authentication failed.');
            return $this->redirectToRoute('app_login');
        }
        try {
            $accessToken = $this->getAccessToken($code);
        } catch (Throwable $error) {
            return $this->redirectToRoute('app_login_microsoft');
        }
        $userData = $this->getUserData($accessToken);
        $userMail = $userData['mail'];
        if (!$userMail) {
            $userMail = $userData['userPrincipalName'];
        }

        $user = $userRepository->findByEmail($userMail);
        if ($user) {
            $user = $user[0];
            if ($user->getLoginFrom() != User::USER_LOGIN_FROM_MS_OFFICE) {
                $this->addFlash("warning", "Opps !! Previously From this Mail You Loged in Via :" . $user->getLoginFrom() . " So Please Logia Via Their Only");
                return $this->redirectToRoute('app_login');
            }
            $source = $userProfilePhotoRepository->findby(['user' => $user]);
            if (!$source) {
                $source = [new UserProfilePhoto()];
            }
            $profileResponse = ($this->getUserProfile($accessToken));
            $profileResponseBase64 = base64_encode($profileResponse);
            $source[0]->setUser($user);
            $source[0]->setSource($profileResponseBase64);
            $source[0]->setAccessToken($accessToken);
            $entityManagerInterface->persist($source[0]);
            $entityManagerInterface->flush();
        } else {
            $user = new User();
            $user->setEmail($userMail);
            $user->setFirstname($userData['surname']);
            $user->setLastName($userData['givenName']);
            $userData['mobilePhone'] != Null ? $user->setContactNumber($userData['mobilePhone']) : null;
            $user->setStatus(User::USER_STATUS_ACTIIVE);
            $user->setRoles([User::ROLE_USER]);
            $user->setLoginFrom(user::USER_LOGIN_FROM_MS_OFFICE);
            $user->setPassword("User Login from " . $user->getLoginFrom());
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();
            $profileResponse = ($this->getUserProfile($accessToken));
            $profileResponseBase64 = base64_encode($profileResponse);
            $source = new UserProfilePhoto();
            $source->setAccessToken($accessToken);
            $source->setUser($user);
            $source->setSource($profileResponseBase64);

            try {
                $entityManagerInterface->persist($source);
                $entityManagerInterface->flush();
            } catch (Throwable $t) {
                return new CustomUserMessageAuthenticationException("opps: " . $t);
            }
            $this->addFlash("Success", "Welcome to Projecto");
        }
        $security->login($user);
        return $this->redirectToRoute('check_user_role');
    }

    private function getAccessToken(string $code): string
    {
        $clientId = $_ENV['MS_OFFICE_CLIENT_ID_LOCAL'];
        $clientSecret = $_ENV['MS_OFFICE_CLIENT_SECRATE_LOCAL'];
        $redirectUri = $this->generateUrl('microsoft_login_completed', [], UrlGeneratorInterface::ABSOLUTE_URL);

        //for hemaxi only
        // $redirectUri = "http://localhost:8000/login/microsoft-login-completed";

        $response = $this->httpClient->request('POST', 'https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'body' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ],
        ]);
        $content = json_decode($response->getContent(), true);
        return $content['access_token'];
    }

    private function getUserData(string $accessToken): array
    {
        $response = $this->httpClient->request('GET', 'https://graph.microsoft.com/v1.0/me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        return json_decode($response->getContent(), true);
    }
    private function getUserProfile(string $accessToken)
    {
        $response = $this->httpClient->request('GET', 'https://graph.microsoft.com/v1.0/me/photos/48x48/\$value', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
        return ($response->getContent());
    }
    private function createEvent(string $accessToken)
    {

        $data = [
            'subject' => 'Meeting',
            'start' => [
                'dateTime' => '2023-06-06T10:00:00',
                'timeZone' => 'UTC'
            ],
            'end' => [
                'dateTime' => '2023-06-06T11:00:00',
                'timeZone' => 'UTC'
            ],
            "attendees" => [
                [
                    "emailAddress" => [
                        "address" => "samanthab@contoso.onmicrosoft.com",
                        "name" => "Samantha Booth"
                    ],
                    "type" => "required"
                ]
            ],
        ];
        $response = $this->httpClient->request('POST', 'https://graph.microsoft.com/v1.0/me/events', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'json' => $data
        ]);
        return ($response->getContent());
    }
}
