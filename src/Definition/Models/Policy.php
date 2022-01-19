<?php


namespace Jinn\Definition\Models;


class Policy
{
    public string $name;
    public bool $anonymous = false;
    public ?string $owner = null; //name of the field to compare with the user
    /** @var string[] */
    public array $roles = [];

    public function __construct($name)
    {
        $this->name = $name;
    }
}
