<?php
/**
 * This file is part of the ForkBB <https://github.com/forkbb>.
 *
 * @copyright (c) Visman <mio.visman@yandex.ru, https://github.com/MioVisman>
 * @license   The MIT License (MIT)
 */

declare(strict_types=1);

namespace ForkBB\Models\Provider\Driver;

use ForkBB\Models\Provider\Driver;
use RuntimeException;

class GitHub extends Driver
{
    protected string $origName = 'github';
    protected string $authURL  = 'https://github.com/login/oauth/authorize';
    protected string $tokenURL = 'https://github.com/login/oauth/access_token';
    protected string $userURL  = 'https://api.github.com/user';
    protected string $scope    = 'read:user';

    /**
     * Запрашивает информацию о пользователе
     * Проверяет ответ
     * Запоминает данные пользователя
     */
    public function reqUserInfo(): bool
    {
        $this->userInfo = [];

        $headers = [
            'Accept: application/json',
            "Authorization: Bearer {$this->access_token}",
            "User-Agent: ForkBB (Client ID: {$this->client_id})",
        ];

        if (empty($ch = \curl_init($this->userURL))) {
            $this->error     = 'cURL error';
            $this->curlError = \curl_error($ch);

            return false;
        }

        \curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);
        \curl_setopt($ch, \CURLOPT_POST, false);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_HEADER, false);

        $result = \curl_exec($ch);

        \curl_close($ch);

        if (false === $result) {
            $this->error     = 'cURL error';
            $this->curlError = \curl_error($ch);

            return false;
        }

        if (
            ! isset($result[1])
            || '{' !== $result[0]
            || '}' !== $result[-1]
            || ! \is_array($userInfo = \json_decode($result, true, 20, self::JSON_OPTIONS))
            || empty($userInfo['id'])
        ) {
            $this->error = 'User error';

            return false;
        }

        $this->userInfo = $userInfo;

        return true;
    }

    /**
     * Возвращает идентификатор пользователя (от провайдера)
     */
    protected function getuserId(): string
    {
        return (string) ($this->userInfo['id'] ?? '');
    }

    /**
     * Возвращает имя пользователя (от провайдера)
     */
    protected function getuserName(): string
    {
        return (string) ($this->userInfo['name'] ?? ($this->userInfo['login'] ?? ''));
    }

    /**
     * Возвращает email пользователя (от провайдера)
     */
    protected function getuserEmail(): string
    {
        return $this->c->Mail->valid($this->userInfo['email'] ?? null) ?: "{$this->origName}-{$this->userId}@localhost";
    }

    /**
     * Возвращает флаг подлинности email пользователя (от провайдера)
     */
    protected function getuserEmailVerifed(): bool
    {
        return false;
    }

    /**
     * Возвращает ссылку на аватарку пользователя (от провайдера)
     */
    protected function getuserAvatar(): string
    {
        return (string) ($this->userInfo['avatar_url'] ?? '');
    }

    /**
     * Возвращает ссылку на профиль пользователя (от провайдера)
     */
    protected function getuserURL(): string
    {
        return $this->userInfo['html_url'];
    }

    /**
     * Возвращает местоположение пользователя (от провайдера)
     */
    protected function getuserLocation(): string
    {
        return (string) ($this->userInfo['location'] ?? '');
    }

    /**
     * Возвращает пол пользователя (от провайдера)
     */
    protected function getuserGender(): int
    {
        return 0;
    }
}