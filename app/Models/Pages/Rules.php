<?php

namespace ForkBB\Models\Pages;

use ForkBB\Models\Page;

class Rules extends Page
{
    /**
     * Подготавливает данные для шаблона
     * 
     * @return Page
     */
    public function view()
    {
        $this->fIndex     = 'rules';
        $this->nameTpl    = 'rules';
        $this->onlinePos  = 'rules';
        $this->canonical  = $this->c->Router->link('Rules');
        $this->titles     = __('Forum rules');
        $this->title      = __('Forum rules');
        $this->rules      = $this->c->config->o_rules_message;
        $this->formAction = null;

        return $this;
    }

    /**
     * Подготавливает данные для шаблона
     * 
     * @return Page
     */
    public function confirmation()
    {
        $this->c->Lang->load('register');

        $this->fIndex     = 'register';
        $this->nameTpl    = 'rules';
        $this->onlinePos  = 'rules';
        $this->robots     = 'noindex';
        $this->titles     = __('Forum rules');
        $this->title      = __('Forum rules');
        $this->rules      = $this->c->config->o_rules == '1' ? $this->c->config->o_rules_message : __('If no rules');
        $this->formAction = $this->c->Router->link('RegisterForm');
        $this->formToken  = $this->c->Csrf->create('RegisterForm');
        $this->formHash   = $this->c->Csrf->create('Register');

        return $this;
    }
}
