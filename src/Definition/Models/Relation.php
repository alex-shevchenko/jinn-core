<?php

namespace Jinn\Definition\Models;

class Relation
{
    public const ONE_TO_MANY = 'one-to-many';
    public const MANY_TO_ONE = 'many-to-one';
    public const MANY_TO_MANY = 'many-to-many';

    public string $name;
    public Entity $entity;
    public string $entityName;
    public string $type;
    public ?string $field = null;
    public bool $noModel = false;

    public function __construct(Entity $entity = null, $type = null, $name = null)
    {
        if ($entity) {
            $this->entity = $entity;
            $this->entityName = $entity->name;
            $this->name = lcfirst($this->entityName);
        }
        if ($type) {
            $this->type = $type;
        }
        if ($name) {
            $this->name = $name;
        }
    }

    public function field(): string
    {
        return ($this->field ?? $this->name) . 'Id';
    }
}
