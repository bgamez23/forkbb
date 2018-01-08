<?php

namespace ForkBB\Models\Forum;

use ForkBB\Models\DataModel;
use RuntimeException;
use InvalidArgumentException;

class Model extends DataModel
{
    /**
     * Получение родительского раздела
     *
     * @throws RuntimeException
     *
     * @return Models\Forum
     */
    protected function getparent()
    {
        if (null === $this->parent_forum_id && $this->id !== 0) {
            throw new RuntimeException('Parent is not defined');
        }

        return $this->c->forums->get($this->parent_forum_id);
    }

    /**
     * Статус возможности создания новой темы
     * 
     * @return bool
     */
    protected function getcanCreateTopic()
    {
        $user = $this->c->user;
        return $this->post_topics == 1
            || (null === $this->post_topics && $user->g_post_topics == 1)
            || $user->isAdmin
            || $user->isModerator($this);
    }

    /**
     * Получение массива подразделов
     *
     * @return array
     */
    protected function getsubforums()
    {
        $sub = [];
        if (! empty($this->a['subforums'])) {
            foreach ($this->a['subforums'] as $id) {
                $sub[$id] = $this->c->forums->get($id);
            }
        }
        return $sub;
    }

    /**
     * Получение массива всех дочерних разделов
     *
     * @return array
     */
    protected function getdescendants()
    {
        $all = [];
        if (! empty($this->a['descendants'])) {
            foreach ($this->a['descendants'] as $id) {
                $all[$id] = $this->c->forums->get($id);
            }
        }
        return $all;
    }

    /**
     * Ссылка на раздел
     *
     * @return string
     */
    protected function getlink()
    {
        return $this->c->Router->link('Forum', ['id' => $this->id, 'name' => $this->forum_name]);
    }

    /**
     * Ссылка на последнее сообщение в разделе
     *
     * @return null|string
     */
    protected function getlinkLast()
    {
        if ($this->last_post_id < 1) {
            return null;
        } else {
            return $this->c->Router->link('ViewPost', ['id' => $this->last_post_id]);
        }
    }

    /**
     * Ссылка на создание новой темы
     * 
     * @return string
     */
    protected function getlinkCreateTopic()
    {
        return $this->c->Router->link('NewTopic', ['id' => $this->id]);
    }

    /**
     * Получение массива модераторов
     *
     * @return array
     */
    protected function getmoderators()
    {
        if (empty($this->a['moderators'])) {
            return [];
        }

        if ($this->c->user->g_view_users == '1') {
            $arr = $this->a['moderators'];
            foreach($arr as $id => &$cur) {
                $cur = [
                    $this->c->Router->link('User', [
                        'id'   => $id,
                        'name' => $cur,
                    ]),
                    $cur,
                ];
            }
            unset($cur);
            return $arr;
        } else {
            return $this->a['moderators'];
        }
    }

    /**
     * Возвращает общую статистику по дереву разделов с корнем в текущем разделе
     *
     * @return Models\Forum
     */
    protected function gettree()
    {
        if (empty($this->a['tree'])) { //????
            $numT   = (int) $this->num_topics;
            $numP   = (int) $this->num_posts;
            $time   = (int) $this->last_post;
            $postId = (int) $this->last_post_id;
            $poster = $this->last_poster;
            $topic  = $this->last_topic;
            $fnew   = $this->newMessages;
            foreach ($this->descendants as $chId => $children) {
                $fnew  = $fnew || $children->newMessages;
                $numT += $children->num_topics;
                $numP += $children->num_posts;
                if ($children->last_post > $time) {
                    $time   = $children->last_post;
                    $postId = $children->last_post_id;
                    $poster = $children->last_poster;
                    $topic  = $children->last_topic;
                }
            }
            $this->a['tree'] = $this->c->forums->create([
                'num_topics'     => $numT,
                'num_posts'      => $numP,
                'last_post'      => $time,
                'last_post_id'   => $postId,
                'last_poster'    => $poster,
                'last_topic'     => $topic,
                'newMessages'    => $fnew,
            ]);
        }
        return $this->a['tree'];
    }

    /**
     * Количество страниц в разделе
     *
     * @throws RuntimeException
     *
     * @return int
     */
    protected function getnumPages()
    {
        if (null === $this->num_topics) {
            throw new RuntimeException('The model does not have the required data');
        }

        return $this->num_topics === 0 ? 1 : (int) ceil($this->num_topics / $this->c->user->disp_topics);
    }

    /**
     * Массив страниц раздела
     *
     * @return array
     */
    protected function getpagination()
    {
        return $this->c->Func->paginate($this->numPages, $this->page, 'Forum', ['id' => $this->id, 'name' => $this->forum_name]);
    }

    /**
     * Статус наличия установленной страницы в разделе
     *
     * @return bool
     */
    public function hasPage()
    {
        return $this->page > 0 && $this->page <= $this->numPages;
    }

    /**
     * Возвращает массив тем с установленной страницы
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public function topics()
    {
        if (! $this->hasPage()) {
            throw new InvalidArgumentException('Bad number of displayed page');
        }

        if (empty($this->num_topics)) {
            return [];
        }

        switch ($this->sort_by) {
            case 1:
                $sortBy = 'posted DESC';
                break;
            case 2:
                $sortBy = 'subject ASC';
                break;
            default:
                $sortBy = 'last_post DESC';
                break;
        }

        $vars = [
            ':fid'    => $this->id,
            ':offset' => ($this->page - 1) * $this->c->user->disp_topics,
            ':rows'   => $this->c->user->disp_topics,
        ];
        $sql = "SELECT id
                FROM ::topics
                WHERE forum_id=?i:fid
                ORDER BY sticky DESC, {$sortBy}, id DESC
                LIMIT ?i:offset, ?i:rows";

        $ids = $this->c->DB->query($sql, $vars)->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($ids)) {
            return []; //????
        }

        $vars = [
            ':uid' => $this->c->user->id,
            ':ids' => $ids,
        ];

        if (! $this->c->user->isGuest && $this->c->config->o_show_dot == '1') {
            $dots = $this->c->DB
                ->query('SELECT topic_id FROM ::posts WHERE poster_id=?i:uid AND topic_id IN (?ai:ids) GROUP BY topic_id', $vars)
                ->fetchAll(\PDO::FETCH_COLUMN);
            $dots = array_flip($dots);
        } else {
            $dots = [];
        }

        if ($this->c->user->isGuest) {
            $sql = "SELECT t.*
                    FROM ::topics AS t
                    WHERE t.id IN(?ai:ids)
                    ORDER BY t.sticky DESC, t.{$sortBy}, t.id DESC";
        } else {
            $sql = "SELECT t.*, mot.mt_last_visit, mot.mt_last_read
                    FROM ::topics AS t
                    LEFT JOIN ::mark_of_topic AS mot ON (mot.uid=?i:uid AND t.id=mot.tid)
                    WHERE t.id IN (?ai:ids)
                    ORDER BY t.sticky DESC, t.{$sortBy}, t.id DESC";
        }
        $topics = $this->c->DB->query($sql, $vars)->fetchAll();

        foreach ($topics as &$cur) {
            $cur['dot'] = isset($dots[$cur['id']]);
            $cur = $this->c->topics->create($cur);
        }
        unset($cur);

        return $topics;
    }
}