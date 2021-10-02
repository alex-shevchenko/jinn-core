<?php

namespace Jinn\Database;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Types\Type;
use Jinn\Models\Entity;
use Jinn\Models\Types;

class DatabaseComparer
{
    private AbstractSchemaManager $schemaManager;
    /** @var callable */
    private $toColumnName;
    /** @var callable */
    private $toFieldName;

    /**
     * DatabaseComparer constructor.
     * @param array $connectionParams
     * @throws \Doctrine\DBAL\Exception
     */
    public function __construct(array $connectionParams, callable $toColumnName, callable $toFieldName)
    {
        $connection = DriverManager::getConnection($connectionParams);
        $this->schemaManager = $connection->createSchemaManager();
        $this->toColumnName = $toColumnName;
        $this->toFieldName = $toFieldName;
    }

    /**
     * @param string $tableName
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function tableExists(string $tableName): bool
    {
        return $this->schemaManager->tablesExist($tableName);
    }

    /**
     * @return ColumnDiff[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function compareTableColumns(Entity $entity, string $tableName): array
    {
        if (!$this->tableExists($tableName)) throw new \LogicException("Table $tableName does not exist");
        $columns = $this->schemaManager->listTableColumns($tableName);
        $fields = $entity->fields();

        $result = [];

        foreach ($fields as $field) {
            $columnName = ($this->toColumnName)($field->name);

            $diff = new ColumnDiff($field);

            if (!isset($columns[$columnName]))
            {
                $result[] = $diff;
                continue;
            }

            $column = $columns[$columnName];
            unset($columns[$columnName]);
            $diff->column = $column;
            $diff->operation = ColumnDiff::OP_CHANGE;

            $fieldType = Type::getType($field->type);
            if ($column->getType() !== $fieldType) {
                $result[] = $diff;
                continue;
            }

            $fieldLength = $field->length ? $field->length : Types::defaultLength($field->type);
            if ($fieldLength && $fieldLength != $column->getLength()) {
                $result[] = $diff;
                continue;
            }

            if ($field->required != $column->getNotnull()) {
                $result[] = $diff;
                continue;
            }

            if ($field->default != $column->getDefault()) {
                $result[] = $diff;
                continue;
            }
        }

        foreach ($columns as $name => $column) {
            $diff = new ColumnDiff();
            $diff->column = $column;
            $diff->operation = ColumnDiff::OP_REMOVE;
            $result[] = $diff;
        }

        return $result;
    }

    /**
     * @param Entity $entity
     * @param string $tableName
     * @return IndexDiff[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function compareTableIndexes(Entity $entity, string $tableName): array
    {
        if (!$this->tableExists($tableName)) throw new \LogicException("Table $tableName does not exist");
        $dbIndexes = $this->schemaManager->listTableIndexes($tableName);
        $foreignKeys = $this->schemaManager->listTableForeignKeys($tableName);
        $fkNames = array_map(function(ForeignKeyConstraint $fk) { return $fk->getName(); }, $foreignKeys);
        $indexes = $entity->indexes();

        $result = [];

        foreach ($dbIndexes as $dbIndex) {
            if ($dbIndex->isPrimary()) continue;
            if (in_array($dbIndex->getName(), $fkNames)) continue; //this index was (most likely) created automatically when creating FK

            $diff = new IndexDiff();
            $diff->dbIndex = $dbIndex;

            $name = $dbIndex->getName();
            if (!isset($indexes[$name])) {
                $diff->operation = IndexDiff::OP_REMOVE;
                $result[] = $diff;
                continue;
            }

            $index = $indexes[$name];
            unset($indexes[$name]);
            $diff->index = $index;
            $diff->operation = IndexDiff::OP_CHANGE;

            if ($dbIndex->isUnique() != $index->isUnique) {
                $result[] = $diff;
                continue;
            }

            if ($dbIndex->getColumns() != array_map($this->toColumnName, $index->columns)) {
                $result[] = $diff;
                continue;
            }
        }

        foreach ($indexes as $index) {
            $result[] = new IndexDiff($index);
        }

        return $result;
    }
}
