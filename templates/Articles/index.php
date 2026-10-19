<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
?>
<?php if ($this->Identity->isLoggedIn()) : ?>
    <?= $this->Html->link(__('Add Article'), ['action' => 'add']) ?>
<?php endif ?>
<h1><?= __('Articles') ?></h1>
<table>
    <tr>
        <th><?= $this->Paginator->sort('title') ?></th>
        <th><?= $this->Paginator->sort('created') ?></th>
        <th><?= __('Action') ?></th>
    </tr>

    <!-- Here is where we iterate through our $articles query object, printing out article info -->

    <?php foreach ($articles as $article) : ?>
    <tr>
        <td>
            <?= $this->Html->link($article->title, ['action' => 'view', $article->slug]) ?>
        </td>
        <td>
            <?= $article->created->format(DATE_RFC850) ?>
        </td>
        <td>
            <!-- Only the author of the article can change it -->
            <?php if ($this->Identity->is($article->user_id)) : ?>
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $article->slug]) ?>
                <?= $this->Form->deleteLink(
                    __('Delete'),
                    ['action' => 'delete', $article->slug],
                    ['confirm' => __('Are you sure?')],
                ) ?>
            <?php endif ?>
        </td>
    </tr>
    <?php endforeach ?>
</table>

<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->first('<< ' . __('first')) ?>
        <?= $this->Paginator->prev('< ' . __('previous')) ?>
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->next(__('next') . ' >') ?>
        <?= $this->Paginator->last(__('last') . ' >>') ?>
    </ul>
</div>
