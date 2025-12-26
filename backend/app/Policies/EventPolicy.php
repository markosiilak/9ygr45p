<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     * Everyone can view events (public listing).
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Everyone can view individual events.
     */
    public function view(?User $user, Event $event): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Only users with create-events permission can create events.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create-events');
    }

    /**
     * Determine whether the user can update the model.
     * Users can update their own events, or admins can update any event.
     */
    public function update(User $user, Event $event): bool
    {
        // Admin can update any event
        if ($user->hasRole('admin')) {
            return true;
        }

        // Event owner can update their own event
        if ($event->user_id === $user->id) {
            return true;
        }

        // Organizer with explicit edit permission (fallback)
        if ($user->hasPermission('edit-events') && $user->hasRole('organizer')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins can delete events.
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->hasPermission('delete-events') && $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Event $event): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $user->hasRole('admin');
    }
}
