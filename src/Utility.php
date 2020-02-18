<?php


namespace bitrix;


use ErrorException;

class Utility
{
    /**
     * @param $argument
     * @return bool
     */
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

    /**
     * @param $argument
     * @return array
     */
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
     * @throws ErrorException
     */
    public static function recursiveUppercaseKey($argument)
    {
        if (is_array($argument) && !self::isPlainArray($argument)) {
            $upperCased = [];
            foreach ($argument as $key => $value) {
                $casedKey = strtoupper($key);
                if (isset($upperCased[$casedKey])) {
                    throw new ErrorException("Map key casing collision for '$key'");
                }
                $upperCased[$casedKey] = self::recursiveUppercaseKey($value);
            }
            return $upperCased;
        }
        return $argument;
    }

    /**
     * @param array  $delimiters
     * @param string $source
     * @return array
     */
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

    /**
     * @param array  $source
     * @param string $column
     * @return array
     */
    public static function unsetColumn(array &$source, string $column)
    {
        foreach ($source as $key => $value) {
            unset($source[$key][$column]);
        }
        return $source;
    }

    /**
     * Возвращает либо единственный элемент в массиве, либо null
     * @param array $items
     * @return mixed|null
     */
    static function disambiguate(array $items)
    {
        if (count($items) !== 1) {
            return null;
        }
        return array_pop($items);
    }

    /**
     * @param $value
     * @return bool
     */
    static function yesNoBoolean($value)
    {
        return $value === 'Y' || $value === true;
    }
}