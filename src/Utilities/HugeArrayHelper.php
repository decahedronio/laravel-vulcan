<?php namespace Decahedron\Vulcan\Utilities;

use ArrayAccess;
use Closure;
use Illuminate\Support\Arr;

/**
 * This is heavily derived from Laravel's Arr::set() method, with extra helpers.
 *
 * @see https://github.com/laravel/framework/blob/5.5/src/Illuminate/Support/Arr.php
 */
class HugeArrayHelper
{
    /**
     * Basic dot-notation setter.
     *
     * It supports using [] to replace a non-assoc sub-array content:
     * `set($a, "foo.[].bar", "baz")` -> `["foo" => [["bar" => "baz"]]`
     * Calling that repeatedly will return the same value.
     *
     * @param $array
     * @param $key
     * @param $value
     */
    public static function set(&$array, $key, $value)
    {
        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if ($key == '[]') {
                array_splice($array, 0);
                $array[0] = [];
                $array = &$array[0];
            } else {
                if (!isset($array[$key]) || !is_array($array[$key])) {
                    $array[$key] = [];
                }
                $array = &$array[$key];
            }
        }


        if (($key = array_shift($keys)) == "[]") {
            array_splice($array, 0);
            $array[0] = $value;
        } else {
            $array[$key] = $value;
        }
    }

    public static function get($array, $key, $default = null)
    {
        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? static::value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return static::value($default);
            }
        }

        return $array;
    }

    /**
     * Basic dot-notation append.
     *
     * It supports using [] to append a non-assoc sub-array content:
     * `set($a, "foo.[].bar", "baz")` -> `["foo" => [["bar" => "baz"]]`
     * Afterwards:
     * `set($a, "foo.[].bar", "baz")` -> `["foo" => [["bar" => "baz"], ["bar" => "baz"]]`
     *
     * @param $array
     * @param $key
     * @param $value
     */
    public static function append(&$array, $key, $value)
    {

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if ($key == '[]') {
                $t = [];
                $array[] = &$t;
                $array = &$t;
            } else {
                if (!isset($array[$key]) || !is_array($array[$key])) {
                    $array[$key] = [];
                }
                $array = &$array[$key];
            }
        }

        $key = array_shift($keys);
        if ($key == "[]") {
                $array[] = $value;
        } else {
            $array[$key] = $value;
        }
    }

    /**
     * @param $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * @param $array
     * @param $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * @param $value
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}