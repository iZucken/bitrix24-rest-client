<?php

namespace bitrix\storage;


/**
 * File storage
 *
 * @package bitrix\storage
 */
class File implements Storage
{
    private $fileName = null;
    private $data = [];

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        if (file_exists($fileName)) {
            $this->data = json_decode(file_get_contents($fileName), true);
        }
    }

    public function set(string $key, $value)
    {
        $this->data[$key] = $value;
        file_put_contents($this->fileName, json_encode($this->data));
    }

    public function get(string $key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }
        return $this->data[$key];
    }
}