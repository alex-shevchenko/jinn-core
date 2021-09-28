<?php

namespace Jinn\Models;

class Relation
{
    public const ONE_TO_MANY = 'one-to-many';
    public const MANY_TO_ONE = 'many-to-one';
    public const MANY_TO_MANY = 'many-to-many';

    public string $name;
    public Entity $entity;
    public string $entityName;
    public string $type;
    public ?string $field;

    public function __construct(Entity $entity = null, $type = null)
    {
        if ($entity) {
            $this->entity = $entity;
            $this->name = $this->entityName = $entity->name;
        }
        if ($type) {
            $this->type = $type;
        }
    }
}
