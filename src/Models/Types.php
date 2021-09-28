<?php


namespace Jinn\Models;


class Types
{
    public const STRING = 'string';
    public const INT = 'integer';
    public const BIGINT = 'bigint';
    public const FLOAT = 'float';
    public const TEXT = 'text';
    public const BOOL = 'boolean';
    public const DATE = 'date';
    public const DATETIME = 'datetime';

    public static function toPhp(string $type): string {
        switch ($type) {
            case self::STRING:
            case self::TEXT:
                return 'string';
            case self::INT:
            case self::BIGINT:
            return 'int';
            case self::FLOAT:
                return 'float';
            case self::BOOL:
                return 'bool';
            case self::DATE:
            case self::DATETIME:
                return '\\DateTime';
            default:
                return null;
        }
    }
}
