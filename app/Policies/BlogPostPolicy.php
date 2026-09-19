<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    /**
     * Admins manage the blog through the landing-content permission they
     * already have; writers get their own role, granted per user from the
     * users screen.
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasRole('blogger');
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, BlogPost $post): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->hasRole('blogger') && $post->author_id === $user->id;
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $this->update($user, $post);
    }

    private function isAdmin(User $user): bool
    {
        return $user->can('edit landing_content');
    }
}
