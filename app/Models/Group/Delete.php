<?php

namespace ForkBB\Models\Group;

use ForkBB\Models\Action;
use ForkBB\Models\Group\Model as Group;
use InvalidArgumentException;
use RuntimeException;

class Delete extends Action
{
    /**
     * Удаляет тему(ы) 
     *
     * @param Group $group
     * @param Group $new
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function delete(Group $group, Group $new = null)
    {
        //????
#       if (! $arg->parent instanceof Forum) {
#           throw new RuntimeException('Parent unavailable');
#       }

        if (null !== $new) {
            $this->c->users->promote($group, $new);
        }

        $vars = [
            ':gid' => $group->g_id,
        ];
        $sql = 'DELETE FROM ::groups
                WHERE g_id=?i:gid';
        $this->c->DB->exec($sql, $vars);
    }
}