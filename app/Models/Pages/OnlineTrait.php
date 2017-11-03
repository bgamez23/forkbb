<?php

namespace ForkBB\Models\Pages;

trait OnlineTrait 
{
    /**
     * Получение информации об онлайн посетителях
     * @return null|array
     */
    protected function usersOnlineInfo() 
    {
        if ($this->c->config->o_users_online == '1') {
            // данные онлайн посетителей
            $this->c->Online->calc($this);
            $users  = $this->c->Online->users; //????
            $guests = $this->c->Online->guests;
            $bots   = $this->c->Online->bots;

            $list   = [];
            $data = [
                'max'      => $this->number($this->c->config->st_max_users),
                'max_time' => $this->time($this->c->config->st_max_users_time),
            ];

            if ($this->c->user->g_view_users == '1') {
                foreach ($users as $id => $cur) {
                    $list[] = [
                        $this->c->Router->link('User', [
                            'id' => $id,
                            'name' => $cur['name'],
                        ]),
                        $cur['name'],
                    ];
                }
            } else {
                foreach ($users as $cur) {
                    $list[] = $cur['name'];
                }
            }
            $data['number_of_users'] = $this->number(count($users));

            $s = 0;
            foreach ($bots as $name => $cur) {
                $count = count($cur);
                $s += $count;
                if ($count > 1) {
                    $list[] = '[Bot] ' . $name . ' (' . $count . ')';
                } else {
                    $list[] = '[Bot] ' . $name;
                }
            }
            $s += count($guests);
            $data['number_of_guests'] = $this->number($s);
            $data['list'] = $list;
            return $data;
        } else {
            $this->onlineType = false;
            return null;
        }
    }
}
