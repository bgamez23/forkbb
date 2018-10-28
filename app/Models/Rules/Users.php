<?php

namespace ForkBB\Models\Rules;

use ForkBB\Models\Model;
use ForkBB\Models\Rules;
use ForkBB\Models\User\Model as User;
use ForkBB\Models\Rules\Profile as ProfileRules;
use RuntimeException;

class Users extends Rules
{
    /**
     * Инициализирует
     *
     * @return UsersRules
     */
    public function init()
    {
        $this->a     = [];
        $this->aCalc = [];
        $this->ready = true;
        $this->user  = $this->c->user;

        return $this;
    }

    protected function getviewIP()
    {
        return $this->user->canViewIP;
    }

    protected function getdeleteUsers()
    {
        return $this->user->isAdmin;
    }

    protected function getbanUsers()
    {
        return $this->user->isAdmin || ($this->user->isAdmMod && '1' == $this->user->g_mod_ban_users);
    }

    protected function getchangeGroup()
    {
        return $this->user->isAdmin;
    }

    public function canDeleteUser(User $user)
    {
        if (! $this->profileRules instanceof ProfileRules) {
            $this->profileRules = $this->c->ProfileRules;
        }

        return $this->profileRules->setUser($user)->deleteUser;
    }

    public function canBanUser(User $user)
    {
        if (! $this->profileRules instanceof ProfileRules) {
            $this->profileRules = $this->c->ProfileRules;
        }

        return $this->profileRules->setUser($user)->banUser;
    }

    public function canChangeGroup(User $user)
    {
        return $this->user->isAdmin && ! $user->isAdmin;
    }
}