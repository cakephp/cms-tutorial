<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\Paging\SortableFieldsBuilder;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Anyone can read the articles, the other actions need a logged in user.
        $this->Authentication->allowUnauthenticated(['index', 'view', 'tags']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();

        // Only let the users sort the articles by title and creation date.
        $articles = $this->paginate($this->Articles->find(), [
            'sortableFields' => fn(SortableFieldsBuilder $fields) => $fields
                ->add('title', 'Articles.title')
                ->add('created', 'Articles.created'),
        ]);

        $this->set(compact('articles'));
    }

    /**
     * View method
     *
     * @param string $slug Article slug.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $slug)
    {
        $this->Authorization->skipAuthorization();

        $article = $this->Articles
            ->findBySlug($slug)
            ->contain('Tags')
            ->firstOrFail();

        $this->set(compact('article'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        $this->Authorization->authorize($article);

        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            // Set the user_id from the logged in user.
            $article->user_id = $this->Authentication->getIdentityData('id');

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }

        $this->set(compact('article'));
    }

    /**
     * Edit method
     *
     * @param string $slug Article slug.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(string $slug)
    {
        $article = $this->Articles
            ->findBySlug($slug)
            ->contain('Tags') // load associated Tags
            ->firstOrFail();
        $this->Authorization->authorize($article);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData(), [
                // Disable modification of user_id.
                'accessibleFields' => ['user_id' => false],
            ]);
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been updated.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to update your article.'));
        }

        // Get a list of tags.
        $tags = $this->Articles->Tags->find('list')->all();

        $this->set(compact('article', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string $slug Article slug.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(string $slug)
    {
        $this->request->allowMethod(['post', 'delete']);

        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        $this->Authorization->authorize($article);

        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The {0} article has been deleted.', $article->title));
        } else {
            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Tags method
     *
     * @param string ...$tags The tags to look for.
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function tags(string ...$tags)
    {
        $this->Authorization->skipAuthorization();

        // Use the ArticlesTable to find tagged articles.
        $articles = $this->Articles->find('tagged', tags: $tags);

        // Pass variables into the view template context.
        $this->set(compact('articles', 'tags'));
    }
}
