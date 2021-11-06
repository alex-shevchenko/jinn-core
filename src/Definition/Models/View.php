<?php


namespace Jinn\Definition\Models;


class View
{
    public string $name;
    public ?array $fields = null;
    public string $fullName;

    public function __construct($entityName, $name, $fields = null)
    {
        $this->name = $name;
        $this->fullName = $entityName . '.' . $name;
        $this->fields = $fields;
    }
}
