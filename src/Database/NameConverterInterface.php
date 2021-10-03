<?php


namespace Jinn\Database;


use Jinn\Definition\Models\Entity;

interface NameConverterInterface
{
    public function tableName(Entity $entity): string;
    public function toColumnName(string $fieldName): string;
    public function toFieldName(string $fieldName): string;
}
