<?php

namespace App\Security;

use Exception;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    const EDIT = 'edit';
    const AUTH = 'authorize';
    const DEL = 'delete';
    // const CREATE = 'create';


    public function __construct(
        private Security $security,
    ) {

    }


    protected function supports(string $attribute, mixed $subject) :bool
    {
        if (in_array($attribute, [self::EDIT, self::AUTH, self::DEL]) === false) {
            return false;
        }

        if ($subject !== null) {
            if (!$subject instanceof User) {
                return false;
            }

        }

        return true;

    }


    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        // if ($attribute !== self::CREATE) {
            $account = $subject;
        // }

        return match ($attribute) {
            self::EDIT => $this->canEdit($account, $user),
            self::AUTH => $this->canAuthorize($account, $user),
            self::DEL => $this->canDelete($account, $user),
            // self::CREATE => $this->canCreate($subject),
            default => throw new Exception('Erreur'),
        };

        return true;
    }

    private function canEdit(User $account, $user) :bool
    {
        if ($account->getId() === $user->getId()) {
            return true;
        }

        return false;

    }


    private function canAuthorize(User $account) :bool
    {
        if (in_array('ROLE_SUPER_ADMIN', $account->getRoles())) {
            return false;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        if ($this->security->isGranted('ROLE_ADMIN') && in_array('ROLE_ADMIN', $account->getRoles()) === false) {
            return true;
        }

        return false;

    }

    private function canDelete(User $account, $user) :bool
    {
        return $this->canAuthorize($account, $user);

    }


    private function canCreate(User|null $subject) :bool
    {
        if ($subject !== null) {
            return $this->security->isGranted('ROLE_ADMIN');
        }

        return true;

    }


}
