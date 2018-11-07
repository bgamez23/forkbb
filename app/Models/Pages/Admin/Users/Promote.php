<?php

namespace ForkBB\Models\Pages\Admin\Users;

use ForkBB\Models\Pages\Admin\Users;

class Promote extends Users
{
    /**
     * Продвигает пользователя
     *
     * @param array $args
     * @param string $method
     *
     * @return Page
     */
    public function promote(array $args, $method)
    {
        if (! $this->c->Csrf->verify($args['token'], 'AdminUserPromote', $args)) {
            return $this->c->Message->message('Bad token');
        }

        $user = $this->c->users->load((int) $args['uid']);
        if (0 < $user->g_promote_next_group * $user->g_promote_min_posts) {
            $user->group_id = $user->g_promote_next_group;
            $this->c->users->update($user);
        }

        return $this->c->Redirect->page('ViewPost', ['id' => $args['pid']])->message('User promote redirect');
    }
}