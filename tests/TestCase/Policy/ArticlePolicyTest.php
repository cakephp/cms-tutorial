<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Article;
use App\Policy\ArticlePolicy;
use Authentication\Identity;
use Authorization\AuthorizationService;
use Authorization\IdentityDecorator;
use Authorization\IdentityInterface;
use Authorization\Policy\OrmResolver;
use Cake\TestSuite\TestCase;

/**
 * App\Policy\ArticlePolicy Test Case
 */
class ArticlePolicyTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Policy\ArticlePolicy
     */
    protected $policy;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ArticlePolicy();
    }

    /**
     * Builds the identity of the logged in user.
     *
     * @param int $id The user id.
     * @return \Authorization\IdentityInterface
     */
    protected function identity(int $id): IdentityInterface
    {
        return new IdentityDecorator(new AuthorizationService(new OrmResolver()), new Identity(['id' => $id]));
    }

    /**
     * Test that any logged in user can add articles
     *
     * @return void
     */
    public function testCanAdd(): void
    {
        $this->assertTrue($this->policy->canAdd($this->identity(1), new Article()));
    }

    /**
     * Test that only the author can edit and delete an article
     *
     * @return void
     */
    public function testOnlyTheAuthorCanEditAndDelete(): void
    {
        $article = new Article(['user_id' => 1]);

        $this->assertTrue($this->policy->canEdit($this->identity(1), $article));
        $this->assertFalse($this->policy->canEdit($this->identity(2), $article));
        $this->assertTrue($this->policy->canDelete($this->identity(1), $article));
        $this->assertFalse($this->policy->canDelete($this->identity(2), $article));
    }
}
