<?php


namespace Jinn\Database\Models;

use Doctrine\DBAL\Schema\Index as DbIndex;
use Jinn\Definition\Models\Index;

class IndexDiff extends DbDiff
{
    public ?Index $index;
    public ?DbIndex $dbIndex;

    public function __construct(?Index $index = null)
    {
        if ($index) {
            $this->index = $index;
            $this->operation = self::OP_ADD;
        }
    }
}
