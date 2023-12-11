<?php

namespace App\Security;

use Exception;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use function PHPUnit\Framework\throwException;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    const EDIT = 'edit';

    public function __construct(
        private Security $security,
    ) {
    }


    protected function supports(string $attribute, mixed $subject) :bool
    { 
        if (in_array($attribute, [self::EDIT]) === false) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;

    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $account = $subject;

        return match ($attribute) {
            self::EDIT => $this->canEdit($account, $user),
            default => throw new Exception('Erreur'),
        };
        return true;
    }

    private function canEdit(User $account, $user) :bool
    {
        if ($account->getId() === $user->getId()) {
            return true;
        }

        if(in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        return false;

    }
}
