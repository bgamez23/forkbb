<?php
/**
 * This file is part of the ForkBB <https://github.com/forkbb>.
 *
 * @copyright (c) Visman <mio.visman@yandex.ru, https://github.com/MioVisman>
 * @license   The MIT License (MIT)
 */

declare(strict_types=1);

namespace ForkBB\Models\Search;

use ForkBB\Models\Method;
use ForkBB\Models\Forum\Forum;
use PDO;
use InvalidArgumentException;

class ActionT extends Method
{
    /**
     * Поисковые действия по темам
     */
    public function actionT(string $action, Forum $root, int $uid = null): array|false
    {
        $forums = \array_keys($root->descendants);

        if ($root->id) {
            $forums[] = $root->id;
        }
        if (empty($forums)) {
            return [];
        }

        $query = null;

        switch ($action) {
            case 'search':
                $list = $this->model->queryIds;

                break;
            case 'latest_active_topics':
                $query = 'SELECT t.id
                    FROM ::topics AS t
                    WHERE t.forum_id IN (?ai:forums) AND t.moved_to=0
                    ORDER BY t.last_post DESC
                    LIMIT 1000';

                break;
            case 'unanswered_topics':
                $query = 'SELECT t.id
                    FROM ::topics AS t
                    WHERE t.forum_id IN (?ai:forums) AND t.moved_to=0 AND t.num_replies=0
                    ORDER BY t.last_post DESC';

                break;
            case 'topics_with_your_posts':
                $query = 'SELECT t.id
                    FROM ::topics AS t
                    INNER JOIN ::posts AS p ON t.id=p.topic_id
                    WHERE t.forum_id IN (?ai:forums) AND t.moved_to=0 AND p.poster_id=?i:uid
                    GROUP BY t.id
                    ORDER BY t.last_post DESC';

                break;
            case 'topics':
                $query = 'SELECT t.id
                    FROM ::topics AS t
                    INNER JOIN ::posts AS p ON t.first_post_id=p.id
                    WHERE t.forum_id IN (?ai:forums) AND t.moved_to=0 AND p.poster_id=?i:uid
                    ORDER BY t.first_post_id DESC'; // t.last_post

                break;
            case 'new':
                $query = 'SELECT t.id
                    FROM ::topics AS t
                    LEFT JOIN ::mark_of_topic AS mot ON (mot.uid=?i:uid AND mot.tid=t.id)
                    LEFT JOIN ::mark_of_forum AS mof ON (mof.uid=?i:uid AND mof.fid=t.forum_id)
                    WHERE t.forum_id IN (?ai:forums)
                        AND t.last_post>?i:max
                        AND t.moved_to=0
                        AND (mot.mt_last_visit IS NULL OR t.last_post>mot.mt_last_visit)
                        AND (mof.mf_mark_all_read IS NULL OR t.last_post>mof.mf_mark_all_read)
                    ORDER BY t.last_post DESC';

                break;
            case 'topics_subscriptions':
                if (0 !== $root->id) {
                    return false;
                }

                $user = $this->c->users->load($uid);

                if (! $this->c->ProfileRules->setUser($user)->viewSubscription) {
                    return false;
                }

                $subscr     = $this->c->subscriptions;
                $subscrInfo = $subscr->info($user, $subscr::TOPICS_DATA);
                $list       = $subscrInfo[$subscr::TOPICS_DATA] ?? [];

                \arsort($list, \SORT_NUMERIC); // ???? или по последнему сообщению делать?

                break;
            default:
                throw new InvalidArgumentException('Unknown action: ' . $action);
        }

        if (null !== $query) {
            $vars = [
                ':forums' => $forums,
                ':uid'    => $uid,
                ':max'    => \max((int) $this->c->user->last_visit, (int) $this->c->user->u_mark_all_read),
            ];

            $list = $this->c->DB->query($query, $vars)->fetchAll(PDO::FETCH_COLUMN);
        }

        $this->model->numPages = (int) \ceil((\count($list) ?: 1) / $this->c->user->disp_topics);

        // нет такой страницы в результате поиска
        if (! $this->model->hasPage()) {
            return false;
        // результат пуст
        } elseif (empty($list)) {
            return [];
        }

        $this->model->idsList = \array_slice(
            $list,
            ($this->model->page - 1) * $this->c->user->disp_topics,
            (int) $this->c->user->disp_topics
        );

        return $this->c->topics->view($this->model);
    }
}
