<?php


namespace bitrix;


class Utility
{
    public static function isPlainArray($argument)
    {
        if (!is_array($argument)) {
            return false;
        }
        $last = -1;
        foreach ($argument as $key => $value) {
            if ($key !== $last + 1) {
                return false;
            }
            $last = $key;
        }
        return true;
    }

    function recursiveNullValueUnset($argument)
    {
        if (is_array($argument) && !Utility::isPlainArray($argument)) {
            $clean = [];
            foreach ($argument as $key => $value) {
                if (!is_null($value)) {
                    $clean[$key] = $this->recursiveNullValueUnset($value);
                }
            }
            return $clean;
        }
        return $argument;
    }

    function recursiveExplode(array $delimiters, string $source)
    {
        if (empty($delimiters)) {
            return [$source];
        }
        $chunks = [];
        foreach (explode(array_pop($delimiters), $source) as $item) {
            $chunks[] = $this->recursiveExplode($delimiters, $item);
        }
        return array_merge(...$chunks);
    }
}