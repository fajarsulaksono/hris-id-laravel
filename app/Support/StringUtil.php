<?php

namespace App\Support;

class StringUtil
{
    public static function uppercase(string $value): string
    {
        return strtoupper($value);
    }

    public static function lowercase(string $value): string
    {
        return strtolower($value);
    }

    public static function underscore(string $value): string
    {
        return strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', str_replace(' ', '_', $value)));
    }

    public static function dash(string $value): string
    {
        return strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1-$2', str_replace(' ', '-', $value)));
    }

    public static function camelcase(string $value): string
    {
        return (string) preg_replace_callback('/_([a-z])/', function ($match) {
            return strtoupper($match[1]);
        }, strtolower($value));
    }

    public static function randomize(?string $prefix): string
    {
        return sha1(uniqid($prefix, true));
    }

    public static function sanitize(string $value): string
    {
        return trim($value);
    }
}