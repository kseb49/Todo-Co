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
    const CREATE = 'create';

    public function __construct(
        private Security $security,
    ) {
    }


    protected function supports(string $attribute, mixed $subject) :bool
    { 
        if (in_array($attribute, [self::EDIT, self::AUTH, self::DEL, self::CREATE]) === false) {
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
        // Check if the user is connected
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $account = $subject;

        return match ($attribute) {
            self::EDIT => $this->canEdit($account, $user),
            self::AUTH => $this->canAuthorize($account, $user),
            self::DEL => $this->canDelete($account, $user),
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


    private function canAuthorize(User $account, $user) :bool
    {
        if(in_array('ROLE_SUPER_ADMIN', $account->getRoles())) {
            return false;
        }

        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        if(in_array('ROLE_ADMIN', $user->getRoles()) && in_array('ROLE_ADMIN', $account->getRoles()) === false) {
            return true;
        }

        return false;

    }

    private function canDelete(User $account, $user) :bool
    {
       return $this->canAuthorize($account, $user);

    }


}
