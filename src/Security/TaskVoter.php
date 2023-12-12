<?php

namespace App\Security;

use App\Entity\Task;
use Exception;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TaskVoter extends Voter
{
    const TOGGLE = 'toggle';
    const DEL = 'delete';
    const CREATE = 'create';
    const EDIT = 'edit';

    public function __construct(
        private Security $security,
    ) {
    }


    protected function supports(string $attribute, mixed $subject) :bool
    {
        if (in_array($attribute, [self::TOGGLE, self::DEL, self::CREATE, self::EDIT]) === false) {
            return false;
        }

        if ($subject) {
            if (!$subject instanceof Task) {
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

        $task = $subject;

        return match ($attribute) {
            self::DEL => $this->canDelete($task, $user),
            self::TOGGLE => $this->canToggle($task, $user),
            self::CREATE => $this->canCreate(),
            self::EDIT=> $this->canEdit($task, $user),
            default => throw new Exception('Erreur'),
        };
        return true;
    }


    private function canToggle(Task $task, $user) :bool
    {
        if ($user == $task->getUser()) {
            return true;
        }

        if ($task->getUser()->getUsername() == 'anonyme' && $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;

    }


    private function canEdit(Task $task, $user) :bool
    {
        return $this->canToggle($task, $user);

    }

    private function canDelete(Task $task, $user) :bool
    {
        return $this->canToggle($task, $user);

    }

    private function canCreate()
    {
        return $this->security->isGranted('ROLE_USER');
    }


}