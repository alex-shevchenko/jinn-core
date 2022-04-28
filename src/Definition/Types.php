<?php


namespace Jinn\Definition;

use Doctrine\DBAL\Types\Types as DbalTypes;

class Types
{
    public const EMAIL = 'email';
    public const STRING = DbalTypes::STRING;
    public const INT = DbalTypes::INTEGER;
    public const BIGINT = DbalTypes::BIGINT;
    public const FLOAT = DbalTypes::FLOAT;
    public const TEXT = DbalTypes::TEXT;
    public const JSON = DbalTypes::JSON;
    public const BOOL = DbalTypes::BOOLEAN;
    public const DATE = DbalTypes::DATE_MUTABLE;
    public const DATETIME = DbalTypes::DATETIME_MUTABLE;

    public static function toDbalType(string $type): string {
        switch ($type) {
            case self::EMAIL:
                return DbalTypes::STRING;
            default:
                return $type;
        }
    }

    public static function toPhp(string $type): ?string {
        switch ($type) {
            case self::EMAIL:
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

    public static function defaultLength(string $type): int {
        switch ($type) {
            case self::EMAIL:
                return 100;
            case self::STRING:
                return 255;
            default:
                return 0;
        }
    }
}
