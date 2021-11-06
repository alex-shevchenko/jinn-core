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
    /** @var View[] */
    protected array $views = [];
    public ?string $extends = null;
    /** @var string[] */
    public array $implements = [];
    /** @var string[] */
    public array $traits = [];
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

    public function addView(View $view): void {
        $name = $view->name;
        if (isset($this->views[$name])) throw new InvalidArgumentException("View $name already exists in entity {$this->name}");
        $this->views[$name] = $view;
    }

    public function index($name): Index {
        if (!isset($this->indexes[$name])) throw new InvalidArgumentException("Index $name does not exists in entity {$this->name}");
        return $this->indexes[$name];
    }
    /**
     * @return Index[]
     */
    public function indexes(): array {
        return $this->indexes;
    }

    public function field($name): Field {
        if (!isset($this->fields[$name])) throw new InvalidArgumentException("Field $name does not exists in entity {$this->name}");
        return $this->fields[$name];
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

    public function view($name): View {
        if (!isset($this->views[$name])) throw new InvalidArgumentException("View $name does not exists in entity {$this->name}");
        return $this->views[$name];
    }

    public function views(): array {
        return $this->views;
    }

    public function hasIndex(string $name): bool {
        return isset($this->indexes[$name]);
    }

    public function hasField(string $name): bool {
        return isset($this->fields[$name]);
    }

    public function hasRelation(string $name): bool {
        return isset($this->relations[$name]);
    }
}
