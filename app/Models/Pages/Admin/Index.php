<?php

namespace ForkBB\Models\Pages\Admin;

class Index extends Admin
{
    /**
     * Имя шаблона
     * @var string
     */
    protected $nameTpl = 'admin/index';

    /**
     * Указатель на активный пункт навигации админки
     * @var string
     */
    protected $adminIndex = 'index';

    /**
     * Подготавливает данные для шаблона
     * @return Page
     */
    public function index()
    {
        $this->c->Lang->load('admin_index');
        $this->data = [
            'revision' => $this->config['i_fork_revision'],
            'linkStat' => $this->c->Router->link('AdminStatistics'),
        ];
        $this->titles[] = __('Admin index');
        return $this;
    }
}
