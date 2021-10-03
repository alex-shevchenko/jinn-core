<?php

namespace Jinn\Definition\Models;

use InvalidArgumentException;

class Entity
{
    public string $name;
    /** @var Field[] */
    protected array $fields = [];
    /** @var Index[] */
    protected array $indexes = [];
    /** @var Relation[] */
    protected array $relations = [];
    public bool $noModel = false;
    public bool $isPivot = false;


    public function addField(Field $field): void {
        $name = $field->name;
        if (isset($this->fields[$name]) || isset($this->relations[$name])) throw new InvalidArgumentException("Field or relation $name already exists in entity {$this->name}");
        $this->fields[$name] = $field;
    }

    public function addIndex(Index $index): void {
        $name = $index->name;
        if (isset($this->indexes[$name])) throw new InvalidArgumentException("Index $name already exists in entity {$this->name}");
        $this->indexes[$name] = $index;
    }

    public function addRelation(Relation $relation): void {
        $name = $relation->name;
        if (isset($this->fields[$name]) || isset($this->relations[$name])) throw new InvalidArgumentException("Field or relation $name already exists in entity {$this->name}");
        $this->relations[$name] = $relation;
    }

    /**
     * @return Index[]
     */
    public function indexes(): array {
        return $this->indexes;
    }

    /**
     * @return Field[]
     */
    public function fields(): array {
        return $this->fields;
    }

    /**
     * @return Relation[]
     */
    public function relations(): array {
        return $this->relations;
    }

    public function hasField(string $name): bool {
        return isset($this->fields[$name]);
    }
}
