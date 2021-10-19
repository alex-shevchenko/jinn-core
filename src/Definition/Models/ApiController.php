<?php


namespace Jinn\Definition\Models;

use InvalidArgumentException;

class ApiController
{
    public Entity $entity;
    /** @var ApiMethod[] */
    protected array $methods = [];
    private array $allFields;

    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
        $this->allFields = [];

        foreach ($entity->fields() as $field) {
            if (!$field->noModel) $this->allFields[] = $field->name;
        }
        foreach ($entity->relations() as $relation) {
            $this->allFields[] = $relation->name;
        }
    }

    public function name(): string {
        return $this->entity->name;
    }

    public function addMethod(ApiMethod $method): void {
        $name = $method->name;
        if (isset($this->methods[$name])) throw new InvalidArgumentException("Method $name already exists in controller {$this->name()}");
        $this->methods[$name] = $method;

        if (!$method->fields) $method->fields = $this->allFields;
    }

    public function hasMethods(): bool {
        return count($this->methods) > 0;
    }

    public function removeMethod($name): void {
        if (!isset($this->methods[$name])) throw new InvalidArgumentException("Method $name does not exist in controller {$this->name()}");
        unset($this->methods[$name]);
    }
    /**
     * @return ApiMethod[]
     */
    public function methods(): array {
        return $this->methods;
    }

    public function fillDefault(): void {
        $this->addMethod(new ApiMethod(ApiMethod::LIST));
        $this->addMethod(new ApiMethod(ApiMethod::GET));
        $this->addMethod(new ApiMethod(ApiMethod::CREATE));
        $this->addMethod(new ApiMethod(ApiMethod::UPDATE));
        $this->addMethod(new ApiMethod(ApiMethod::DELETE));
    }
}
