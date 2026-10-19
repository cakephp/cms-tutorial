<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\UsersController Test Case
 *
 * @link \App\Controller\UsersController
 */
class UsersControllerTest extends TestCase
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
    ];

    /**
     * Logs in Alice.
     *
     * @return void
     */
    protected function loginAsAlice(): void
    {
        $this->session(['Auth' => ['id' => 1, 'email' => 'alice@example.com']]);
    }

    /**
     * Test login with the right credentials
     *
     * @return void
     */
    public function testLogin(): void
    {
        $this->enableCsrfToken();

        $this->post('/users/login', ['email' => 'alice@example.com', 'password' => 'secret']);

        $this->assertRedirect(['controller' => 'Articles', 'action' => 'index']);
    }

    /**
     * Test login redirects to the page the user was trying to open
     *
     * @return void
     */
    public function testLoginRedirectsBack(): void
    {
        $this->enableCsrfToken();

        $this->post('/users/login?redirect=%2Farticles%2Fadd', ['email' => 'alice@example.com', 'password' => 'secret']);

        $this->assertRedirect('/articles/add');
    }

    /**
     * Test login with the wrong password
     *
     * @return void
     */
    public function testLoginFails(): void
    {
        $this->enableCsrfToken();

        $this->post('/users/login', ['email' => 'alice@example.com', 'password' => 'wrong']);

        $this->assertResponseOk();
        // The flash message is rendered in the layout.
        $this->assertResponseContains('Invalid username or password');
    }

    /**
     * Test logout method
     *
     * @return void
     */
    public function testLogout(): void
    {
        $this->loginAsAlice();

        $this->get('/users/logout');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertSession(null, 'Auth');
    }

    /**
     * Test the sign up
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->enableCsrfToken();

        $this->post('/users/add', ['email' => 'carol@example.com', 'password' => 'secret']);

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $user = $this->getTableLocator()->get('Users')->findByEmail('carol@example.com')->firstOrFail();
        $this->assertNotSame('secret', $user->password, 'The password must be hashed.');
    }

    /**
     * Test that the users list needs a logged in user
     *
     * @return void
     */
    public function testIndexRequiresLogin(): void
    {
        $this->get('/users');

        $this->assertRedirectContains('/users/login');
    }

    /**
     * Test that the users list only contains the logged in user
     *
     * @return void
     */
    public function testIndexIsScoped(): void
    {
        $this->loginAsAlice();

        $this->get('/users');

        $this->assertResponseOk();
        $this->assertResponseContains('alice@example.com');
        $this->assertResponseNotContains('bob@example.com');
    }

    /**
     * Test that a user can see the own profile with the articles linked by slug
     *
     * @return void
     */
    public function testView(): void
    {
        $this->loginAsAlice();

        $this->get('/users/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('/articles/view/First-Article');
    }

    /**
     * Test that a user can edit the own profile
     *
     * @return void
     */
    public function testEdit(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->put('/users/edit/1', ['email' => 'alice@example.org']);

        $this->assertRedirect(['controller' => 'Users', 'action' => 'index']);
        $this->assertSame('alice@example.org', $this->getTableLocator()->get('Users')->get(1)->email);
    }

    /**
     * Test that the edit form does not print the password hash
     *
     * @return void
     */
    public function testEditDoesNotShowThePasswordHash(): void
    {
        $this->loginAsAlice();

        $this->get('/users/edit/1');

        $this->assertResponseOk();
        $this->assertResponseNotContains('$2y$');
    }

    /**
     * Test that an empty password keeps the current one
     *
     * @return void
     */
    public function testEditKeepsThePasswordWhenItIsEmpty(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();
        $users = $this->getTableLocator()->get('Users');
        $hash = $users->get(1)->password;

        $this->put('/users/edit/1', ['email' => 'alice@example.org', 'password' => '']);

        $this->assertRedirect(['controller' => 'Users', 'action' => 'index']);
        $this->assertSame($hash, $users->get(1)->password);
    }

    /**
     * Test that a user cannot edit another user
     *
     * @return void
     */
    public function testEditOtherUser(): void
    {
        $this->loginAsAlice();

        $this->get('/users/edit/2');

        $this->assertResponseCode(403);
    }

    /**
     * Test view method with an invalid id
     *
     * @return void
     */
    public function testViewWithInvalidId(): void
    {
        $this->loginAsAlice();

        $this->get('/users/view/abc');

        $this->assertResponseCode(404);
    }

    /**
     * Test that a user can delete the own account, with the articles and their tags
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->delete('/users/delete/1');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertSession(null, 'Auth');
        $this->assertFalse($this->getTableLocator()->get('Users')->exists(['id' => 1]));
        $this->assertFalse($this->getTableLocator()->get('Articles')->exists(['user_id' => 1]), 'The articles are deleted.');
        $this->assertFalse($this->getTableLocator()->get('ArticlesTags')->exists(['article_id' => 1]), 'The article tags are deleted.');
        $this->assertTrue($this->getTableLocator()->get('Articles')->exists(['user_id' => 2]), 'The articles of the other users stay.');
    }

    /**
     * Test that a user cannot delete another user
     *
     * @return void
     */
    public function testDeleteOtherUser(): void
    {
        $this->loginAsAlice();
        $this->enableCsrfToken();

        $this->delete('/users/delete/2');

        $this->assertResponseCode(403);
        $this->assertTrue($this->getTableLocator()->get('Users')->exists(['id' => 2]));
    }
}
