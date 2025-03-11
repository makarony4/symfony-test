<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';


    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof UserInterface) {
            return false;
        }

        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]);
//        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
            if ($user === $subject) {
                return true;
            }
            throw new AccessDeniedException('You do not have permission to access this user.');
            case self::DELETE:
                if ($user === $subject) {
                    return true;
                }
                throw new AccessDeniedException('You do not have permission to delete users.');
        }

        throw new \LogicException('You are not allowed to change this attribute');
    }
}