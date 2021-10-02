<?php


namespace Jinn\Database;


class DbDiff
{
    public const OP_ADD = 'add';
    public const OP_CHANGE = 'change';
    public const OP_REMOVE = 'remove';

    public string $operation;
}
