<?php

namespace App\Security;

use App\Entity\User;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class UserCheckerStatus implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }
        if ($user->getStatus() == User::USER_STATUS_INACTIIVE) {

            throw new CustomUserMessageAccountStatusException('Your account is not activated.');
        }
        if (!$user->getDepartment()) {
            throw new CustomUserMessageAccountStatusException('Your Still Pending to Assign Department.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // user account is expired, the user may be notified
        // if ($user->isExpired()) {
        //     throw new AccountExpiredException('...');
        // }
    }
}