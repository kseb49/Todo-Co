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
    const GIVE_ACCESS = 'give';

    public function __construct(
        private Security $security,
    ) {
    }


    protected function supports(string $attribute, mixed $subject) :bool
    {
        if (in_array('$attribute', [self::GIVE_ACCESS]) === false) {
            return false;
        }

        // if (!$subject instanceof User) {
        //     return false;
        // }

        return true;

    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN') === false) {
            return false;
        }

        // $users = $subject;

        // return match ($attribute) {
        //     self::GIVE_ACCESS => $this->canAuthorize(),
        //     default => throw new Exception('Erreur'),
        // };
        return true;
    }


}
