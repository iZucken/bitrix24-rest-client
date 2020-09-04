<?php

namespace bitrix\rest;

class OauthFullCredentials
{
    public $user = null;
    public $password = null;
    public $id = null;
    public $secret = null;

    function __construct(
        string $id,
        string $secret,
        string $user,
        string $password
    ) {
        $this->id = $id;
        $this->secret = $secret;
        $this->user = $user;
        $this->password = $password;
    }
}