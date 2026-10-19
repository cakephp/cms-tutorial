<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\TagsController Test Case
 *
 * @link \App\Controller\TagsController
 */
class TagsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Tags',
        'app.ArticlesTags',
    ];

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->get('/tags');

        $this->assertResponseOk();
        $this->assertResponseContains('CakePHP');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $this->get('/tags/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('First Article');
        // The articles are found by their slug.
        $this->assertResponseContains('/articles/view/First-Article');
    }

    /**
     * Test view method with an unknown or invalid id
     *
     * @return void
     */
    public function testViewNotFound(): void
    {
        $this->get('/tags/view/999');
        $this->assertResponseCode(404);

        $this->get('/tags/view/abc');
        $this->assertResponseCode(404);
    }

    /**
     * Test that a tag title cannot be used twice
     *
     * @return void
     */
    public function testAddDuplicatedTag(): void
    {
        $this->session(['Auth' => ['id' => 1, 'email' => 'alice@example.com']]);
        $this->enableCsrfToken();

        $this->post('/tags/add', ['title' => 'PHP']);

        $this->assertResponseOk();
        $this->assertResponseContains('This tag already exists.');
    }

    /**
     * Test that the tags list does not offer the delete link, nobody can delete tags
     *
     * @return void
     */
    public function testIndexHasNoDeleteLink(): void
    {
        $this->session(['Auth' => ['id' => 1, 'email' => 'alice@example.com']]);

        $this->get('/tags');

        $this->assertResponseOk();
        $this->assertResponseNotContains('/tags/delete/');
    }

    /**
     * Test add method requires a logged in user
     *
     * @return void
     */
    public function testAddRequiresLogin(): void
    {
        $this->get('/tags/add');

        $this->assertRedirectContains('/users/login');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->session(['Auth' => ['id' => 1, 'email' => 'alice@example.com']]);
        $this->enableCsrfToken();

        $this->post('/tags/add', ['title' => 'Testing']);

        $this->assertRedirect(['controller' => 'Tags', 'action' => 'index']);
        $this->assertTrue($this->getTableLocator()->get('Tags')->exists(['title' => 'Testing']));
    }

    /**
     * Test that editing a tag cannot change the articles, they belong to their authors
     *
     * @return void
     */
    public function testEditCannotChangeTheArticles(): void
    {
        $this->session(['Auth' => ['id' => 2, 'email' => 'bob@example.com']]);
        $this->enableCsrfToken();

        $this->get('/tags/edit/2');
        $this->assertResponseOk();
        $this->assertResponseNotContains('articles[_ids]');

        $this->put('/tags/edit/2', ['title' => 'PHP', 'articles' => ['_ids' => [2]]]);

        $this->assertRedirect(['controller' => 'Tags', 'action' => 'index']);
        $articles = $this->getTableLocator()->get('Tags')->get(2, contain: ['Articles'])->articles;
        $this->assertSame([1], array_column($articles, 'id'), 'Only the first article keeps the tag.');
    }

    /**
     * Test delete method, which nobody is allowed to do
     *
     * @return void
     */
    public function testDeleteIsForbidden(): void
    {
        $this->session(['Auth' => ['id' => 1, 'email' => 'alice@example.com']]);
        $this->enableCsrfToken();

        $this->delete('/tags/delete/1');

        $this->assertResponseCode(403);
        $this->assertTrue($this->getTableLocator()->get('Tags')->exists(['id' => 1]));
    }
}
