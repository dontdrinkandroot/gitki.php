<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Utils;


class StringUtils
{

    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

} 