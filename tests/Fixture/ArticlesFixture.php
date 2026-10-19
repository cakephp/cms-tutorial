<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArticlesFixture
 */
class ArticlesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'title' => 'First Article',
                'slug' => 'First-Article',
                'body' => 'Body of the first article',
                'published' => true,
                'created' => '2026-01-02 10:00:00',
                'modified' => '2026-01-02 10:00:00',
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'title' => 'Second Article',
                'slug' => 'Second-Article',
                'body' => 'Body of the second article',
                'published' => true,
                'created' => '2026-01-03 10:00:00',
                'modified' => '2026-01-03 10:00:00',
            ],
        ];
        parent::init();
    }
}
