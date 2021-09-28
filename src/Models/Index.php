<?php

namespace Jinn\Models;

class Index
{
    public string $name;
    /** @var string[] */
    public array $columns;
    public bool $isUnique = false;

    /**
     * Index constructor.
     * @param string $name
     * @param string[] $columns
     * @param bool $isUnique
     */
    public function __construct(string $name = null, array $columns = null, bool $isUnique = false)
    {
        if ($name)
            $this->name = $name;
        if ($columns)
            $this->columns = $columns;
        $this->isUnique = $isUnique;
    }
}
