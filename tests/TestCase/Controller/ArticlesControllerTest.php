<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ArticlesController Test Case
 *
 * @link \App\Controller\ArticlesController
 */
class ArticlesControllerTest extends TestCase
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
     * Logs in Alice, the author of the first article.
     *
     * @return void
     */
    protected function loginAsAlice(): void
    {
        $this->session(['Auth' => ['id' => 1, 'email' => 'alice@example.com']]);
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->get('/articles');

        $this->assertResponseOk();
        $this->assertResponseContains('First Article');
        $this->assertResponseContains('Second Article');
    }

    /**
     * Test sorting the articles by a sortable field
     *
     * @return void
     */
    public function testIndexSortedByTitle(): void
    {
        $this->get('/articles?sort=title&direction=desc');

        $this->assertResponseOk();
        $body = (string)$this->_response->getBody();
        $this->assertLessThan(strpos($body, 'First Article'), strpos($body, 'Second Article'));
    }

    /**
     * Test that the articles cannot be sorted by a field that is not allowed
     *
     * @return void
     */
    public function testIndexIgnoresUnknownSortField(): void
    {
        $this->get('/articles?sort=body&direction=desc');

        $this->assertResponseOk();
    }

    /**
     * Test that only the author sees the edit and delete links
     *
     * @return void
     */
    public function testIndexShowsActionsToTheAuthorOnly(): void
    {
        $this->loginAsAlice();

        $this->get('/articles');

        $this->assertResponseContains('/articles/edit/First-Article');
        $this->assertResponseContains('/articles/delete/First-Article');
        $this->assertResponseNotContains('/articles/edit/Second-Article');
        $this->assertResponseNotContains('/articles/delete/Second-Article');
    }

    /**
     * Test that the root URL lists the articles
     *
     * @return void
     */
    public function testRoot(): void
    {
        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('First Article');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $this->get('/articles/view/First-Article');

        $this->assertResponseOk();
        $this->assertResponseContains('Body of the first article');
    }

    /**
     * Test view method with an unknown slug
     *
     * @return void
     */
    public function testViewNotFound(): void
    {
        $this->get('/articles/view/unknown');

        $this->assertResponseCode(404);
    }

    /**
     * Test tags method
     *
     * @return void
     */
    public function testTags(): void
    {
        $this->get('/articles/tagged/CakePHP');

        $this->assertResponseOk();
        $this->assertResponseContains('First Article');
        $this->assertResponseNotContains('Second Article');
    }

    /**
     * Test tags method with several tags in the URL
     *
     * @return void
     */
    public function testTagsWithSeveralTags(): void
    {
        $this->get('/articles/tagged/Unknown/PHP');

        $this->assertResponseOk();
        $this->assertResponseContains('First Article');
        $this->assertResponseContains('Unknown or PHP');
    }

    /**
     * Test add method requires a logged in user
     *
     * @return void
     */
    public function testAddRequiresLogin(): void
    {
        $this->get('/articles/add');

        $this->assertRedirectContains('/users/login');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->get('/articles/add');
        $this->assertResponseOk();

        $this->post('/articles/add', [
            'title' => 'Third Article',
            'body' => 'Body of the third article',
            'tag_string' => 'CakePHP, Testing',
        ]);

        $this->assertRedirect(['controller' => 'Articles', 'action' => 'index']);
        $this->assertFlashMessage('Your article has been saved.');

        $article = $this->getTableLocator()->get('Articles')
            ->findBySlug('Third-Article')
            ->contain('Tags')
            ->firstOrFail();
        $this->assertSame(1, $article->user_id);
        $this->assertSame('CakePHP, Testing', $article->tag_string);
    }

    /**
     * Test add method with invalid data
     *
     * @return void
     */
    public function testAddInvalid(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->post('/articles/add', ['title' => '']);

        $this->assertResponseOk();
        // The flash message is rendered in the layout.
        $this->assertResponseContains('Unable to add your article.');
    }

    /**
     * Test that a title that already exists shows the problem in the form
     *
     * @return void
     */
    public function testAddWithTheTitleOfAnotherArticle(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->post('/articles/add', ['title' => 'Second Article', 'body' => 'Duplicated']);

        $this->assertResponseOk();
        $this->assertResponseContains('An article with this title already exists.');
    }

    /**
     * Test that a title without letters or numbers shows the problem in the form
     *
     * @return void
     */
    public function testAddWithoutLettersOrNumbersInTheTitle(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->post('/articles/add', ['title' => '!!! ???', 'body' => 'Nothing to build a slug from']);

        $this->assertResponseOk();
        $this->assertResponseContains('The title needs at least one letter or number.');
    }

    /**
     * Test edit method by the author
     *
     * @return void
     */
    public function testEdit(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->get('/articles/edit/First-Article');
        $this->assertResponseOk();

        $this->put('/articles/edit/First-Article', ['title' => 'Updated Article', 'user_id' => 2]);

        $this->assertRedirect(['controller' => 'Articles', 'action' => 'index']);
        $article = $this->getTableLocator()->get('Articles')->get(1);
        $this->assertSame('Updated Article', $article->title);
        $this->assertSame(1, $article->user_id, 'The author must not be modified.');
    }

    /**
     * Test that emptying the tag field removes the tags
     *
     * @return void
     */
    public function testEditWithAnEmptyTagString(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->put('/articles/edit/First-Article', ['title' => 'First Article', 'tag_string' => '']);

        $this->assertRedirect(['controller' => 'Articles', 'action' => 'index']);
        $this->assertSame([], $this->getTableLocator()->get('Articles')->get(1, contain: ['Tags'])->tags);
    }

    /**
     * Test edit method by someone who is not the author
     *
     * @return void
     */
    public function testEditByOtherUser(): void
    {
        $this->loginAsAlice();

        $this->get('/articles/edit/Second-Article');

        $this->assertResponseCode(403);
    }

    /**
     * Test delete method by the author
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->delete('/articles/delete/First-Article');

        $this->assertRedirect(['controller' => 'Articles', 'action' => 'index']);
        $this->assertFalse($this->getTableLocator()->get('Articles')->exists(['id' => 1]));
    }

    /**
     * Test delete method by someone who is not the author
     *
     * @return void
     */
    public function testDeleteByOtherUser(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->delete('/articles/delete/Second-Article');

        $this->assertResponseCode(403);
        $this->assertTrue($this->getTableLocator()->get('Articles')->exists(['id' => 2]));
    }
}
