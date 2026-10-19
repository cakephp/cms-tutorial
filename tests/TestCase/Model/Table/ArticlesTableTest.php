<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArticlesTable Test Case
 */
class ArticlesTableTest extends TestCase
{
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
     * Test subject
     *
     * @var \App\Model\Table\ArticlesTable
     */
    protected $Articles;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Articles = $this->getTableLocator()->get('Articles');
    }

    /**
     * Test that the slug is built from the title
     *
     * @return void
     */
    public function testSlugIsBuiltFromTitle(): void
    {
        $article = $this->Articles->newEntity(['title' => 'Hello CakePHP World', 'user_id' => 1]);

        $this->assertSame('Hello-CakePHP-World', $article->slug);
    }

    /**
     * Test validation of a missing title
     *
     * @return void
     */
    public function testTitleIsRequired(): void
    {
        $article = $this->Articles->newEntity(['user_id' => 1]);

        $this->assertArrayHasKey('title', $article->getErrors());
    }

    /**
     * Test that the slug must be unique
     *
     * @return void
     */
    public function testSlugMustBeUnique(): void
    {
        $article = $this->Articles->newEntity(['title' => 'First Article', 'user_id' => 1]);

        $this->assertFalse($this->Articles->save($article));
        $this->assertSame(
            ['_isUnique' => 'An article with this title already exists.'],
            $article->getError('title'),
        );
    }

    /**
     * Test that a title without letters or numbers is rejected, the slug would be empty
     *
     * @return void
     */
    public function testTitleNeedsLettersOrNumbers(): void
    {
        $article = $this->Articles->newEntity(['title' => '!!! ???', 'user_id' => 1]);

        $this->assertFalse($this->Articles->save($article));
        $this->assertSame(
            ['slugNotEmpty' => 'The title needs at least one letter or number.'],
            $article->getError('title'),
        );
    }

    /**
     * Test that the title cannot be longer than the slug column
     *
     * @return void
     */
    public function testTitleCannotBeLongerThanTheSlugColumn(): void
    {
        $this->assertEmpty($this->Articles->newEntity(['title' => str_repeat('a', 191)])->getError('title'));
        $this->assertArrayHasKey('maxLength', $this->Articles->newEntity(['title' => str_repeat('a', 192)])->getError('title'));
    }

    /**
     * Test that the slug cannot be set from the request data
     *
     * @return void
     */
    public function testSlugCannotBeMassAssigned(): void
    {
        $article = $this->Articles->newEntity(['slug' => 'custom', 'title' => 'Hello Slug', 'user_id' => 1]);
        $this->assertSame('Hello-Slug', $article->slug);

        $article = $this->Articles->get(1);
        $this->Articles->patchEntity($article, ['slug' => 'custom']);
        $this->assertSame('First-Article', $article->slug);
    }

    /**
     * Test that tags are built from the tag string, reusing the existing ones
     *
     * @return void
     */
    public function testTagStringBuildsTags(): void
    {
        $article = $this->Articles->newEntity([
            'title' => 'Tagged Article',
            'user_id' => 1,
            'tag_string' => 'CakePHP, Testing, Testing, ',
        ]);

        $this->assertNotFalse($this->Articles->save($article));

        $tags = $this->Articles->get($article->id, contain: ['Tags'])->tags;
        $this->assertCount(2, $tags);
        $tagsTable = $this->getTableLocator()->get('Tags');
        $this->assertSame(1, $tagsTable->find()->where(['title' => 'CakePHP'])->count(), 'The existing tag is reused.');
        $this->assertSame(3, $tagsTable->find()->count(), 'Only the new tag is created.');
    }

    /**
     * Test that the tags are not duplicated when only the case is different
     *
     * @return void
     */
    public function testTagStringIgnoresTheCase(): void
    {
        $article = $this->Articles->newEntity([
            'title' => 'Tagged Article',
            'user_id' => 1,
            'tag_string' => 'PHP, php, Fresh, FRESH',
        ]);

        $this->assertNotFalse($this->Articles->save($article));

        $tags = $this->Articles->get($article->id, contain: ['Tags'])->tags;
        $this->assertSame(['PHP', 'Fresh'], array_column($tags, 'title'));
        $this->assertSame(3, $this->getTableLocator()->get('Tags')->find()->count());
    }

    /**
     * Test that an existing tag is reused when the case is different, in every database
     *
     * @return void
     */
    public function testTagStringReusesTheExistingTagIgnoringTheCase(): void
    {
        $article = $this->Articles->newEntity(['title' => 'Tagged Article', 'user_id' => 1, 'tag_string' => 'php, cakephp']);

        $this->assertNotFalse($this->Articles->save($article));

        $tags = $this->Articles->get($article->id, contain: ['Tags'])->tags;
        $this->assertEqualsCanonicalizing(['PHP', 'CakePHP'], array_column($tags, 'title'));
        $this->assertSame(2, $this->getTableLocator()->get('Tags')->find()->count(), 'No new tags are created.');
    }

    /**
     * Test that an empty tag string removes the tags of the article
     *
     * @return void
     */
    public function testEmptyTagStringRemovesTheTags(): void
    {
        $article = $this->Articles->get(1, contain: ['Tags']);
        $this->assertCount(2, $article->tags);

        $this->Articles->patchEntity($article, ['tag_string' => '']);
        $this->assertNotFalse($this->Articles->save($article));

        $this->assertSame([], $this->Articles->get(1, contain: ['Tags'])->tags);
    }

    /**
     * Test that the tags stay when the tag string is not sent
     *
     * @return void
     */
    public function testTagsStayWhenTheTagStringIsNotSent(): void
    {
        $article = $this->Articles->get(1, contain: ['Tags']);

        $this->Articles->patchEntity($article, ['title' => 'First Article Updated']);
        $this->assertNotFalse($this->Articles->save($article));

        $this->assertCount(2, $this->Articles->get(1, contain: ['Tags'])->tags);
    }

    /**
     * Test a tag string without any tag
     *
     * @return void
     */
    public function testTagStringWithoutTags(): void
    {
        $article = $this->Articles->newEntity(['title' => 'Tagged Article', 'user_id' => 1, 'tag_string' => ' , , ']);

        $this->assertNotFalse($this->Articles->save($article));
        $this->assertSame([], $this->Articles->get($article->id, contain: ['Tags'])->tags);
    }

    /**
     * Test the tag string of an article
     *
     * @return void
     */
    public function testTagString(): void
    {
        $article = $this->Articles->get(1, contain: ['Tags']);

        $this->assertSame('CakePHP, PHP', $article->tag_string);
    }

    /**
     * Test the tagged finder
     *
     * @return void
     */
    public function testFindTagged(): void
    {
        $tagged = $this->Articles->find('tagged', tags: ['PHP'])->all();
        $this->assertCount(1, $tagged);
        $this->assertSame('First Article', $tagged->first()->title);

        $untagged = $this->Articles->find('tagged')->all();
        $this->assertCount(1, $untagged);
        $this->assertSame('Second Article', $untagged->first()->title);
    }
}
