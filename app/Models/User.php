<?php

namespace ForkBB\Models;

use ForkBB\Core\AbstractModel;
use R2\DependencyInjection\ContainerInterface;
use RuntimeException;

class User extends AbstractModel
{
    /**
     * Контейнер
     * @var ContainerInterface
     */
    protected $c;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var UserCookie
     */
    protected $userCookie;

    /**
     * @var DB
     */
    protected $db;

    /**
     * Время
     * @var int
     */
    protected $now;

    /**
     * Конструктор
     */
    public function __construct(array $data, ContainerInterface $container)
    {
        $this->now = time();
        $this->c = $container;
        $this->config = $container->get('config');
        $this->userCookie = $container->get('UserCookie');
        $this->db = $container->get('DB');
        parent::__construct($data);
    }

    /**
     * Выполняется до конструктора родителя
     */
    protected function beforeConstruct(array $data)
    {
        return $data;
    }

    protected function getIsUnverified()
    {
        return $this->groupId == PUN_UNVERIFIED;
    }

    protected function getIsGuest()
    {
        return $this->id < 2 || empty($this->gId) || $this->gId == PUN_GUEST;
    }

    protected function getIsAdmin()
    {
        return $this->gId == PUN_ADMIN;
    }

    protected function getIsAdmMod()
    {
        return $this->gId == PUN_ADMIN || $this->gModerator == '1';
    }

    protected function getLogged()
    {
        return empty($this->data['logged']) ? $this->now : $this->data['logged'];
    }

    protected function getIsLogged()
    {
        return ! empty($this->data['logged']);
    }

    protected function getLanguage()
    {
        if ($this->isGuest
            || ! file_exists($this->c->getParameter('DIR_LANG') . '/' . $this->data['language'] . '/common.po')
        ) {
            return $this->config['o_default_lang'];
        } else {
            return $this->data['language'];
        }
    }

    protected function getStyle()
    {
        if ($this->isGuest
//???            || ! file_exists($this->c->getParameter('DIR_LANG') . '/' . $this->data['language'])
        ) {
            return $this->config['o_default_style'];
        } else {
            return $this->data['style'];
        }
    }
}