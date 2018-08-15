<?php

namespace ForkBB\Models\Pages\Profile;

use ForkBB\Core\Image;
use ForkBB\Core\Validator;
use ForkBB\Core\Exceptions\MailException;
use ForkBB\Models\Pages\Profile;
use ForkBB\Models\User\Model as User;

class Pass extends Profile
{
    /**
     * Подготавливает данные для шаблона смены пароля
     *
     * @param array $args
     * @param string $method
     *
     * @return Page
     */
    public function pass(array $args, $method)
    {
        if (false === $this->initProfile($args['id']) || ! $this->rules->editPass) {
            return $this->c->Message->message('Bad request');
        }

        if ('POST' === $method) {
            $v = $this->c->Validator->reset()
                ->addValidators([
                    'check_password' => [$this, 'vCheckPassword'],
                ])->addRules([
                    'token'     => 'token:EditUserPass',
                    'password'  => 'required|string:trim|check_password',
                    'new_pass'  => 'required|string:trim,lower|password',
                ])->addAliases([
                    'new_pass'  => 'New pass',
                    'password'  => 'Your passphrase',
                ])->addArguments([
                    'token'           => ['id' => $this->curUser->id],
                ])->addMessages([
                ]);

            if ($v->validation($_POST)) {
//                if (\password_verify($v->new_pass, $this->curUser->password)) {
//                    return $this->c->Redirect->page('EditUserProfile', ['id' => $this->curUser->id])->message('Email is old redirect');
//                }

                $this->curUser->password = \password_hash($v->new_pass, \PASSWORD_DEFAULT);
                $this->c->users->update($this->curUser);

                if ($this->rules->my) {
#                    $auth = $this->c->Auth;
#                    $auth->fIswev = ['s' => [\ForkBB\__('Pass updated')]];
#                    return $auth->login(['_username' => $this->curUser->username], 'GET');
                    return $this->c->Redirect->page('Login')->message('Pass updated'); // ???? нужна передача данных между скриптами не привязанная к пользователю
                } else {
                    return $this->c->Redirect->page('EditUserProfile', ['id' => $this->curUser->id])->message('Pass updated redirect');
                }
            }

            $this->fIswev = $v->getErrors();
        }

        $this->crumbs     = $this->crumbs(
            [$this->c->Router->link('EditUserPass', ['id' => $this->curUser->id]), \ForkBB\__('Change pass')],
            [$this->c->Router->link('EditUserProfile', ['id' => $this->curUser->id]), \ForkBB\__('Editing profile')]
        );
        $this->form       = $this->form();
        $this->actionBtns = $this->btns('edit');

        return $this;
    }

    /**
     * Создает массив данных для формы
     *
     * @return array
     */
    protected function form()
    {
        $form = [
            'action' => $this->c->Router->link('EditUserPass', ['id' => $this->curUser->id]),
            'hidden' => [
                'token' => $this->c->Csrf->create('EditUserPass', ['id' => $this->curUser->id]),
            ],
            'sets'   => [
                'new-pass' => [
                    'class'  => 'data-edit',
                    'fields' => [
                        'new_pass' => [
                            'type'      => 'password',
                            'maxlength' => 25,
                            'caption'   => \ForkBB\__('New pass'),
                            'required'  => true,
                            'pattern'   => '^.{16,}$',
                            'info'      => \ForkBB\__('Pass format') . ' ' . \ForkBB\__('Pass info'),
                        ],
                        'password' => [
                            'type'      => 'password',
                            'caption'   => \ForkBB\__('Your passphrase'),
                            'required'  => true,
                        ],
                    ],
                ],
            ],
            'btns'   => [
                'submit' => [
                    'type'      => 'submit',
                    'value'     => \ForkBB\__('Submit'),
                    'accesskey' => 's',
                ],
            ],
        ];

        return $form;
    }
}