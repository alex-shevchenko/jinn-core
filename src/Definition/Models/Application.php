<?php


namespace Jinn\Definition\Models;

use InvalidArgumentException;

class Application
{
    /** @var Entity[] */
    protected array $entities = [];
    /** @var ApiController[] */
    protected array $apiControllers = [];

    public function addEntity(Entity $entity): void {
        $name = $entity->name;
        if (isset($this->entities[$name])) throw new InvalidArgumentException("Entity $name already exists in application");
        $this->entities[$name] = $entity;
    }

    public function hasEntity($name): bool {
        return isset($this->entities[$name]);
    }

    public function entity($name): Entity {
        if (!isset($this->entities[$name])) throw new InvalidArgumentException("Entity $name not found");
        return $this->entities[$name];
    }

    /**
     * @return Entity[]
     */
    public function entities(): array {
        return $this->entities;
    }

    public function addApiController(ApiController $apiController): void {
        $name = $apiController->name();
        if (isset($this->apiControllers[$name])) throw new InvalidArgumentException("ApiController $name already exists in application");
        $this->apiControllers[$name] = $apiController;
    }

    /**
     * @return ApiController[]
     */
    public function apiControllers(): array {
        return $this->apiControllers;
    }
}
