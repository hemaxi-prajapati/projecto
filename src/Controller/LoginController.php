<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;
use League\OAuth2\Client\Provider\Google;


class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted("IS_AUTHENTICATED_FULLY")) {
            return $this->redirectToRoute("check_user_role");
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUser = $authenticationUtils->getLastUsername();
        return $this->render('login/index.html.twig', [
            'last_username' => $lastUser,
            'error' => $error
        ]);
    }

    #[Route('/login/microsoft', name: 'app_login_microsoft')]
    public function microsoftLogin(): RedirectResponse
    {

        if ($this->isGranted("IS_AUTHENTICATED_FULLY")) {
            return $this->redirectToRoute("check_user_role");
        }
        $redirectUri = $this->generateUrl('microsoft_login_completed', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $redirectUri = "http://localhost:8000/login/microsoft-login-completed";
        $authorizationUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?' . http_build_query([
            'client_id' => $_ENV['MS_OFFICE_CLIENT_ID_LOCAL'],
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'response_mode' => 'query',
            'scope' => 'user.read Calendars.ReadWrite',
        ]);
        try {
            return new RedirectResponse($authorizationUrl);
        } catch (Throwable $error) {
            $this->addFlash("warning", "Opps!! Authentication Failed.. due to " . $error);
        }
    }


    #[Route('/login/google', name: 'app_login_google')]
    public function googleLogin(Google $provider): RedirectResponse
    {
        $redirectUri = $this->generateUrl('google_login_completed', [], UrlGeneratorInterface::ABSOLUTE_URL);
        // $redirectUri = "http://localhost:8000/login/google-login-completed";
        $provider = new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRATE'],
        ]);
        $authorizationUrl = $provider->getAuthorizationUrl([
            'redirect_uri' => $redirectUri,
        ]);
        return new RedirectResponse($authorizationUrl);
    }
    // #[Route('/logout', name: 'app_logout')]
    // public function logout()
    // {
    // }
}
