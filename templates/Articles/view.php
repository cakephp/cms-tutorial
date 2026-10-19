<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<h1><?= h($article->title) ?></h1>
<p><?= h($article->body) ?></p>
<p><b><?= __('Tags:') ?></b> <?= h($article->tag_string) ?></p>
<p><small><?= __('Created:') ?> <?= $article->created->format(DATE_RFC850) ?></small></p>
<?php if ($this->Identity->is($article->user_id)) : ?>
    <p><?= $this->Html->link(__('Edit'), ['action' => 'edit', $article->slug]) ?></p>
<?php endif ?>
