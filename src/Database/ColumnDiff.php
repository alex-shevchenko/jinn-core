<?php

namespace Jinn\Database;

use Doctrine\DBAL\Schema\Column;
use Jinn\Models\Field;

class ColumnDiff extends DbDiff
{
    public ?Field $field;
    public ?Column $column;

    public function __construct(?Field $field = null)
    {
        if ($field) {
            $this->field = $field;
            $this->operation = self::OP_ADD;
        }
    }
}
