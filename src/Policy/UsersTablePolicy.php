<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

/**
 * Users table policy
 */
class UsersTablePolicy
{
    /**
     * Scope the users list to the logged in user
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \Cake\ORM\Query\SelectQuery $query The query to scope.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function scopeIndex(IdentityInterface $user, SelectQuery $query): SelectQuery
    {
        return $query->where(['Users.id' => $user['id']]);
    }
}
