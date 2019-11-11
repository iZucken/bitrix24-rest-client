<?php


namespace bitrix;


use bitrix\exception\BitrixClientException;

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

    public static function recursiveNullHashMapValueUnset($argument)
    {
        if (is_array($argument) && !self::isPlainArray($argument)) {
            $clean = [];
            foreach ($argument as $key => $value) {
                if (!is_null($value)) {
                    $clean[$key] = self::recursiveNullHashMapValueUnset($value);
                }
            }
            return $clean;
        }
        return $argument;
    }

    /**
     * @param $argument
     * @return array
     * @throws BitrixClientException
     */
    public static function recursiveUppercaseKey($argument)
    {
        if (is_array($argument) && !self::isPlainArray($argument)) {
            $upperCased = [];
            foreach ($argument as $key => $value) {
                $casedKey = strtoupper($key);
                if (isset($upperCased[$casedKey])) {
                    throw new BitrixClientException("Map key casing collision for '$key'");
                }
                $upperCased[$casedKey] = self::recursiveUppercaseKey($value);
            }
            return $upperCased;
        }
        return $argument;
    }

    public static function recursiveExplode(array $delimiters, string $source)
    {
        if (empty($delimiters)) {
            return [$source];
        }
        $chunks = [];
        foreach (explode(array_pop($delimiters), $source) as $item) {
            $chunks[] = self::recursiveExplode($delimiters, $item);
        }
        return array_merge(...$chunks);
    }

    public static function unsetColumn(array &$source, string $column)
    {
        foreach ($source as $key => $value) {
            unset($source[$key][$column]);
        }
        return $source;
    }
}