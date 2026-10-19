<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        // Both users have the password `secret`.
        $password = (new DefaultPasswordHasher())->hash('secret');
        $this->records = [
            [
                'id' => 1,
                'email' => 'alice@example.com',
                'password' => $password,
                'created' => '2026-01-01 10:00:00',
                'modified' => '2026-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'email' => 'bob@example.com',
                'password' => $password,
                'created' => '2026-01-01 10:00:00',
                'modified' => '2026-01-01 10:00:00',
            ],
        ];
        parent::init();
    }
}
