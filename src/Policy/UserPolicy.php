<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;

/**
 * User policy
 */
class UserPolicy
{
    /**
     * Check if $user can view User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\User $resource The user being accessed.
     * @return bool
     */
    public function canView(IdentityInterface $user, User $resource): bool
    {
        return $this->isSelf($user, $resource);
    }

    /**
     * Check if $user can edit User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\User $resource The user being accessed.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, User $resource): bool
    {
        return $this->isSelf($user, $resource);
    }

    /**
     * Check if $user can delete User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\User $resource The user being accessed.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, User $resource): bool
    {
        return $this->isSelf($user, $resource);
    }

    /**
     * Check if $resource is the logged in user
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\User $resource The user being accessed.
     * @return bool
     */
    protected function isSelf(IdentityInterface $user, User $resource): bool
    {
        return $resource->id === $user['id'];
    }
}
