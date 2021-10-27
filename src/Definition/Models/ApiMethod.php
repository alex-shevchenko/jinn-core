<?php

namespace Jinn\Definition\Models;

class ApiMethod
{
    public const LIST = 'list';
    public const GET = 'get';
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    public string $name;
    public string $type;
    public ?array $fields = null;
    public ?Policy $policy = null;
    public ?string $relation = null;
    public $route = null;

    public function __construct(string $name, string $type = null)
    {
        if ($type == null) $type = $name;
        $this->name = $name;
        $this->type = $type;
    }
}
