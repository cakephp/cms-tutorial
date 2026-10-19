<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Article;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Articles Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\BelongsToMany $Tags
 * @method \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Article> findBySlug(string $slug)
 * @method \App\Model\Entity\Article newEmptyEntity()
 * @method \App\Model\Entity\Article newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Article> newEntities(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Article get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Article findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Article patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Article> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Article|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Article saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ArticlesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('articles');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'article_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'articles_tags',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('title')
            // The slug is built from the title and its column is shorter than the title one.
            ->maxLength('title', 191)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 191)
            ->notEmptyString('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('body')
            ->allowEmptyString('body');

        $validator
            ->boolean('published')
            ->allowEmptyString('published');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // The slug is built from the title, so the title needs some letters or numbers.
        $rules->add(
            fn(Article $article): bool => $article->slug !== '',
            'slugNotEmpty',
            ['errorField' => 'title', 'message' => __('The title needs at least one letter or number.')],
        );
        // The title is also the field the user can fix when the slug is already used.
        $rules->add($rules->isUnique(['slug']), [
            'errorField' => 'title',
            'message' => __('An article with this title already exists.'),
        ]);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Builds the tags from the `tag_string` virtual field before saving.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event.
     * @param \App\Model\Entity\Article $entity The entity being saved.
     * @param \ArrayObject<string, mixed> $options The save options.
     * @return void
     */
    public function beforeSave(EventInterface $event, Article $entity, ArrayObject $options): void
    {
        // Only change the tags when the tag string was sent, an empty one removes all the tags.
        if ($entity->isDirty('tag_string')) {
            $entity->tags = $this->_buildTags($entity->tag_string);
        }
    }

    /**
     * Finds the existing tags and creates the new ones from a comma separated string.
     *
     * @param string $tagString The tags, separated by commas.
     * @return array<\App\Model\Entity\Tag>
     */
    protected function _buildTags(string $tagString): array
    {
        // Trim tags
        $newTags = array_map(trim(...), explode(',', $tagString));
        // Remove all empty tags
        $newTags = array_filter($newTags);
        // Reduce duplicated tags, ignoring the case
        $newTags = array_intersect_key($newTags, array_unique(array_map(mb_strtolower(...), $newTags)));

        // There is nothing to look for when the string only had commas and spaces.
        if (!$newTags) {
            return [];
        }

        // Find the existing tags with a lower case comparison, so every database works the same.
        $lowerCaseTags = array_map(mb_strtolower(...), $newTags);
        $tags = $this->Tags->find()
            ->where(['LOWER(Tags.title) IN' => $lowerCaseTags])
            ->all();

        // Remove existing tags from the list of new tags, ignoring the case.
        $existing = array_map(mb_strtolower(...), $tags->extract('title')->toList());
        $newTags = array_filter($newTags, fn(string $tag): bool => !in_array(mb_strtolower($tag), $existing, true));

        // Add existing tags.
        $out = $tags->toList();
        // Add new tags.
        foreach ($newTags as $tag) {
            $out[] = $this->Tags->newEntity(['title' => $tag]);
        }

        return $out;
    }

    /**
     * Finds the articles having any of the given tags, or no tags at all.
     *
     * The $query argument is a query builder instance.
     * The $tags argument will contain the 'tags' option we passed
     * to find('tagged') in our controller action.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to modify.
     * @param array<string> $tags The tag titles to look for.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findTagged(SelectQuery $query, array $tags = []): SelectQuery
    {
        $columns = [
            'Articles.id', 'Articles.user_id', 'Articles.title',
            'Articles.body', 'Articles.published', 'Articles.created',
            'Articles.slug',
        ];
        $query = $query
            ->select($columns)
            ->distinct($columns);

        if (empty($tags)) {
            // If there are no tags provided, find articles that have no tags.
            $query->leftJoinWith('Tags')
                ->where(['Tags.title IS' => null]);
        } else {
            // Find articles that have one or more of the provided tags.
            $query->innerJoinWith('Tags')
                ->where(['Tags.title IN' => $tags]);
        }

        return $query->groupBy(['Articles.id']);
    }
}
