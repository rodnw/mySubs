<?php

namespace App\Security;

use App\Entity\Member;
use App\Entity\Showcase;
use App\Entity\SoundSystem;
use App\Entity\Subwoofer;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Règles d'accès du cahier des charges :
 * - une collection et ses caissons sont privés : propriétaire ou administrateur ;
 * - un caisson présent dans une vitrine publiée est visible par les membres connectés ;
 * - une vitrine non publiée n'est visible que par son créateur (ou un administrateur).
 */
class AccessChecker
{
    public function __construct(private Security $security)
    {
    }

    public function getMember(): ?Member
    {
        $user = $this->security->getUser();

        return $user instanceof Member ? $user : null;
    }

    public function isAdmin(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    public function canManageSoundSystem(?SoundSystem $soundSystem): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        $member = $this->getMember();

        return null !== $member && null !== $soundSystem && $member->getSoundSystem() === $soundSystem;
    }

    public function canManageSubwoofer(Subwoofer $subwoofer): bool
    {
        return $this->canManageSoundSystem($subwoofer->getSoundSystem());
    }

    public function canViewSubwoofer(Subwoofer $subwoofer): bool
    {
        if ($this->canManageSubwoofer($subwoofer)) {
            return true;
        }
        if (null === $this->getMember()) {
            return false;
        }
        foreach ($subwoofer->getShowcases() as $showcase) {
            if ($showcase->isPublished()) {
                return true;
            }
        }

        return false;
    }

    public function canManageShowcase(Showcase $showcase): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        $member = $this->getMember();

        return null !== $member && $showcase->getCreator() === $member;
    }

    public function canViewShowcase(Showcase $showcase): bool
    {
        return $this->canManageShowcase($showcase)
            || ($showcase->isPublished() && null !== $this->getMember());
    }
}
