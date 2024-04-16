<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArticlesTagsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArticlesTagsTable Test Case
 */
class ArticlesTagsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ArticlesTagsTable
     */
    protected $ArticlesTags;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.ArticlesTags',
        'app.Articles',
        'app.Tags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ArticlesTags') ? [] : ['className' => ArticlesTagsTable::class];
        $this->ArticlesTags = $this->getTableLocator()->get('ArticlesTags', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ArticlesTags);

        parent::tearDown();
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
