<?php


namespace Jinn\Models;


class Field
{
    public string $name;
    public string $type;
    public int $length = 0;
    /** @var mixed */
    public $default = null;
    public bool $required = true;
    public bool $primary = false;
    public bool $noModel = false;

    public function __construct(string $name = null, string $type = null)
    {
        if ($name)
            $this->name = $name;
        if ($type)
            $this->type = $type;
    }
}
