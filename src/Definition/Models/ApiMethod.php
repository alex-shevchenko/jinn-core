<?php

namespace Jinn\Definition\Models;

class ApiMethod
{
    public const LIST = 'list';
    public const GET = 'get';
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const RELATED_LIST = 'relatedList';

    public bool $auth = false;
    public string $name;
    public string $type;
    public ?string $viewName = null;
    public ?array $properties = null;
    public ?View $view = null;
    public ?Policy $policy = null;
    public ?string $relation = null;
    public $route = null;

    public function __construct(string $name, string $type = null, $view = null)
    {
        if ($type == null) $type = $name;
        $this->name = $name;
        $this->type = $type;
        $this->view = $view;
    }
}
