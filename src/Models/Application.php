<?php


namespace Jinn\Models;


class Application
{
    /** @var Entity[] */
    protected array $entities = [];

    public function addEntity(Entity $entity): void {
        $name = $entity->name;
        if (isset($this->entities[$name])) throw new \InvalidArgumentException("Entity $name already exists in application");
        $this->entities[$name] = $entity;
    }

    public function hasEntity($name): bool {
        return isset($this->entities[$name]);
    }

    public function entity($name): Entity {
        if (!isset($this->entities[$name])) throw new \InvalidArgumentException("Entity $name not found");
        return $this->entities[$name];
    }

    /**
     * @return Entity[]
     */
    public function entities(): array {
        return $this->entities;
    }
}
