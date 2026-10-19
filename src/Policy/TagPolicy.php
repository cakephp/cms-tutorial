<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Tag;
use Authorization\IdentityInterface;

/**
 * Tag policy
 */
class TagPolicy
{
    /**
     * Check if $user can add Tag
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Tag $tag The tag.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Tag $tag): bool
    {
        // All logged in users can create tags.
        return true;
    }

    /**
     * Check if $user can edit Tag
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Tag $tag The tag.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Tag $tag): bool
    {
        // All logged in users can edit tags.
        return true;
    }

    /**
     * Check if $user can delete Tag
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Tag $tag The tag.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Tag $tag): bool
    {
        // Tags are shared between articles, nobody can delete them.
        return false;
    }
}
