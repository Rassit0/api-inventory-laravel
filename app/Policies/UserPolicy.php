<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Determinar si el usuario puede ver algún modelo.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     * Determinar si el usuario puede ver el modelo.
     */
    public function view(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     * Determinar si el usuario puede crear modelos.
     */
    public function create(User $user): bool
    {
        if ($user->can("edit-articles")) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Determinar si el usuario puede actualizar el modelo.
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Determinar si el usuario puede eliminar el modelo.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     * Determinar si el usuario puede restaurar el modelo.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Determinar si el usuario puede eliminar el modelo de forma permanente.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
