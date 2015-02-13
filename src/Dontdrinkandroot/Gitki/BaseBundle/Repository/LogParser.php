<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Repository;

class LogParser
{

    const COMMIT_BEGIN = "commit_begin";
    const COMMIT_END = "commit_end";
    const HASH_BEGIN = "hash_begin";
    const HASH_END = "hash_end";
    const AUTHOR_BEGIN = "author_begin";
    const AUTHOR_END = "author_end";
    const MAIL_BEGIN = "mail_begin";
    const MAIL_END = "mail_end";
    const MESSAGE_BEGIN = "message_begin";
    const MESSAGE_END = "message_end";
    const DATE_BEGIN = "date_begin";
    const DATE_END = "date_end";

    public static function getFormatString()
    {
        $s = self::COMMIT_BEGIN;
        $s .= self::HASH_BEGIN . '%H' . self::HASH_END;
        $s .= self::AUTHOR_BEGIN . '%an' . self::AUTHOR_END;
        $s .= self::MAIL_BEGIN . '%ae' . self::MAIL_END;
        $s .= self::DATE_BEGIN . '%ct' . self::DATE_END;
        $s .= self::MESSAGE_BEGIN . '%s' . self::MESSAGE_END;
        $s .= self::COMMIT_END;

        return $s;
    }

    public static function getMatchString()
    {
        $s = '/';
        $s .= self::COMMIT_BEGIN;
        $s .= self::HASH_BEGIN . '(.*?)' . self::HASH_END;
        $s .= self::AUTHOR_BEGIN . '(.*?)' . self::AUTHOR_END;
        $s .= self::MAIL_BEGIN . '(.*?)' . self::MAIL_END;
        $s .= self::DATE_BEGIN . '(.*?)' . self::DATE_END;
        $s .= self::MESSAGE_BEGIN . '(.*?)' . self::MESSAGE_END;
        $s .= self::COMMIT_END;
        $s .= '/s';

        return $s;
    }
}
