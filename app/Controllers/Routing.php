<?php

namespace ForkBB\Controllers;

use ForkBB\Core\Container;

class Routing
{
    /**
     * Контейнер
     * @var Container
     */
    protected $c;

    /**
     * Конструктор
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->c = $container;
    }

    /**
     * Маршрутиризация
     *
     * @return Page
     */
    public function routing()
    {
        $user = $this->c->user;
        $config = $this->c->config;
        $r = $this->c->Router;

        // регистрация/вход/выход
        if ($user->isGuest) {
            // вход
            $r->add('GET',  '/login', 'Auth:login', 'Login');
            $r->add('POST', '/login', 'Auth:loginPost');
            // забыли кодовую фразу
            $r->add('GET',  '/login/forget', 'Auth:forget', 'Forget');
            $r->add('POST', '/login/forget', 'Auth:forgetPost');
            // смена кодовой фразы
            $r->add('GET',  '/login/{email}/{key}/{hash}', 'Auth:changePass', 'ChangePassword');
            $r->add('POST', '/login/{email}/{key}/{hash}', 'Auth:changePassPost');

            // регистрация
            if ($config->o_regs_allow == '1') {
                $r->add('GET',  '/registration', 'Rules:confirmation', 'Register');
                $r->add('POST', '/registration/agree', 'Register:reg', 'RegisterForm');
                $r->add('GET',  '/registration/activate/{id:\d+}/{key}/{hash}', 'Register:activate', 'RegActivate');
            }
        } else {
            // выход
            $r->add('GET', '/logout/{token}', 'Auth:logout', 'Logout');

            // обработка "кривых" перенаправлений с логина и регистрации
            $r->add('GET', '/login[/{tail:.*}]', 'Redirect:toIndex');
            $r->add('GET', '/registration[/{tail:.*}]', 'Redirect:toIndex');
        }
        // просмотр разрешен
        if ($user->g_read_board == '1') {
            // главная
            $r->add('GET', '/', 'Index:view', 'Index');
            // правила
            if ($config->o_rules == '1' && (! $user->isGuest || $config->o_regs_allow == '1')) {
                $r->add('GET', '/rules', 'Rules:view', 'Rules');
            }
            // поиск
            if ($user->g_search == '1') {
                $r->add('GET', '/search', 'Search:view', 'Search');
            }
            // юзеры
            if ($user->g_view_users == '1') {
                // список пользователей
                $r->add('GET', '/userlist[/{page:[1-9]\d*}]', 'Userlist:view', 'Userlist');
                // юзеры
                $r->add('GET', '/user/{id:[1-9]\d*}/{name}', 'Profile:view', 'User'); //????
            }

            // разделы
            $r->add('GET',           '/forum/{id:[1-9]\d*}/{name}[/{page:[1-9]\d*}]', 'Forum:view',    'Forum'   );
            $r->add(['GET', 'POST'], '/forum/{id:[1-9]\d*}/new/topic',                'Post:newTopic', 'NewTopic');
            // темы
            $r->add('GET',  '/topic/{id:[1-9]\d*}/{name}[/{page:[1-9]\d*}]',     'Topic:viewTopic',  'Topic'          );
            $r->add('GET',  '/topic/{id:[1-9]\d*}/view/new',                     'Topic:viewNew',    'TopicViewNew'   );
            $r->add('GET',  '/topic/{id:[1-9]\d*}/view/unread',                  'Topic:viewUnread', 'TopicViewUnread');
            $r->add('GET',  '/topic/{id:[1-9]\d*}/view/last',                    'Topic:viewLast',   'TopicViewLast'  );
            $r->add('GET',  '/topic/{id:[1-9]\d*}/new/reply[/{quote:[1-9]\d*}]', 'Post:newReply',    'NewReply'       );
            $r->add('POST', '/topic/{id:[1-9]\d*}/new/reply',                    'Post:newReply'                      );
            // сообщения
            $r->add('GET',           '/post/{id:[1-9]\d*}#p{id}',  'Topic:viewPost', 'ViewPost'  );
            $r->add(['GET', 'POST'], '/post/{id:[1-9]\d*}/edit',   'Edit:edit',      'EditPost'  );
            $r->add(['GET', 'POST'], '/post/{id:[1-9]\d*}/delete', 'Delete:delete',  'DeletePost');
            $r->add('GET',           '/post/{id:[1-9]\d*}/report', 'Report:report',  'ReportPost');

        }
        // админ и модератор
        if ($user->isAdmMod) {
            $r->add('GET', '/admin/', 'AdminIndex:index', 'Admin');
            $r->add('GET', '/admin/statistics', 'AdminStatistics:statistics', 'AdminStatistics');
        }
        // только админ
        if ($user->isAdmin) {
            $r->add('GET',           '/admin/statistics/info',                 'AdminStatistics:info',   'AdminInfo'         );
            $r->add(['GET', 'POST'], '/admin/options',                         'AdminOptions:edit',      'AdminOptions'      );
            $r->add(['GET', 'POST'], '/admin/permissions',                     'AdminPermissions:edit',  'AdminPermissions'  );
            $r->add(['GET', 'POST'], '/admin/categories',                      'AdminCategories:view',   'AdminCategories'   );
            $r->add(['GET', 'POST'], '/admin/categories/{id:[1-9]\d*}/delete', 'AdminCategories:delete', 'AdminCategoriesDelete');

            $r->add('GET',           '/admin/forums',                          'AdminForums:view',       'AdminForums'       );
            $r->add('GET',           '/admin/groups',                          'AdminGroups:view',       'AdminGroups'       );
            $r->add('POST',          '/admin/groups/default',                  'AdminGroups:defaultSet', 'AdminGroupsDefault');
            $r->add('POST',          '/admin/groups/new[/{base:[1-9]\d*}]',    'AdminGroups:edit',       'AdminGroupsNew'    );
            $r->add(['GET', 'POST'], '/admin/groups/{id:[1-9]\d*}/edit',       'AdminGroups:edit',       'AdminGroupsEdit'   );
            $r->add(['GET', 'POST'], '/admin/groups/{id:[1-9]\d*}/delete',     'AdminGroups:delete',     'AdminGroupsDelete' );
            $r->add('GET',           '/admin/censoring',                       'AdminCensoring:view',    'AdminCensoring'    );

        }

        $uri = $_SERVER['REQUEST_URI'];
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        $uri    = rawurldecode($uri);
        $method = $_SERVER['REQUEST_METHOD'];

        $route = $r->route($method, $uri);
        $page = null;
        switch ($route[0]) {
            case $r::OK:
                // ... 200 OK
                list($page, $action) = explode(':', $route[1], 2);
                $page = $this->c->$page->$action($route[2], $method);
                break;
            case $r::NOT_FOUND:
                // ... 404 Not Found
                if ($user->g_read_board != '1' && $user->isGuest) {
                    $page = $this->c->Redirect->page('Login');
                } else {
                    $page = $this->c->Message->message('Bad request');
                }
                break;
            case $r::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                $page = $this->c->Message->message('Bad request', true, 405, ['Allow: ' . implode(',', $route[1])]);
                break;
            case $r::NOT_IMPLEMENTED:
                // ... 501 Not implemented
                $page = $this->c->Message->message('Bad request', true, 501);
                break;
        }
        return $page;
    }
}
