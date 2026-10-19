<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Article;
use Authorization\IdentityInterface;

/**
 * Article policy
 */
class ArticlePolicy
{
    /**
     * Check if $user can add Article
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Article $article): bool
    {
        // All logged in users can create articles.
        return true;
    }

    /**
     * Check if $user can edit Article
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Article $article): bool
    {
        // logged in users can edit their own articles.
        return $this->isAuthor($user, $article);
    }

    /**
     * Check if $user can delete Article
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Article $article): bool
    {
        // logged in users can delete their own articles.
        return $this->isAuthor($user, $article);
    }

    /**
     * Check if $user is the author of the Article
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    protected function isAuthor(IdentityInterface $user, Article $article): bool
    {
        return $article->user_id === $user['id'];
    }
}
