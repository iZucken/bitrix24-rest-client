<?php


namespace bitrix\storage;


interface Storage
{
    public function set(string $key, $value);

    public function get(string $key);
}