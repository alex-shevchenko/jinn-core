<?php


namespace Jinn\Database\Models;


use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Jinn\Definition\Models\Relation;

class RelationDiff extends DbDiff
{
    public ?Relation $relation;
    public ?ForeignKeyConstraint $foreignKey;
    public ?Index $index;

    public function __construct(?Relation $relation = null)
    {
        if ($relation) {
            $this->relation = $relation;
            $this->operation = self::OP_ADD;
        }
    }
}
