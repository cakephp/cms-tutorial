<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 * @var array<string> $tags
 */
?>
<h1>
    <?= __('Articles tagged with') ?>
    <?= $this->Text->toList(h($tags), __('or')) ?>
</h1>

<section>
<?php foreach ($articles as $article) : ?>
    <article>
        <!-- Use the HtmlHelper to create a link -->
        <h4><?= $this->Html->link(
            $article->title,
            ['controller' => 'Articles', 'action' => 'view', $article->slug],
        ) ?></h4>
        <span><?= h($article->created) ?></span>
    </article>
<?php endforeach ?>
</section>
