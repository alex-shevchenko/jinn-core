<?php

namespace Jinn\Database;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Types\Type;
use Jinn\Database\Models\ColumnDiff;
use Jinn\Database\Models\IndexDiff;
use Jinn\Database\Models\RelationDiff;
use Jinn\Definition\Models\Entity;
use Jinn\Definition\Models\Relation;
use Jinn\Definition\Types;
use LogicException;

class DatabaseComparer
{
    private AbstractSchemaManager $schemaManager;
    private NameConverterInterface $nameConverter;

    /**
     * DatabaseComparer constructor.
     * @param array $connectionParams
     * @param NameConverterInterface $nameConverter;
     * @throws DBALException
     */
    public function __construct(array $connectionParams, NameConverterInterface $nameConverter)
    {
        $connection = DriverManager::getConnection($connectionParams);
        $this->schemaManager = $connection->createSchemaManager();
        $this->nameConverter = $nameConverter;
    }

    /**
     * @param string $tableName
     * @return bool
     * @throws DBALException
     */
    public function tableExists(string $tableName): bool
    {
        return $this->schemaManager->tablesExist($tableName);
    }

    /**
     * @param Entity $entity
     * @return ColumnDiff[]
     * @throws DBALException
     */
    public function compareTableColumns(Entity $entity): array
    {
        $tableName = $this->nameConverter->tableName($entity);

        if (!$this->tableExists($tableName)) throw new LogicException("Table $tableName does not exist");
        $columns = $this->schemaManager->listTableColumns($tableName);
        $fields = $entity->fields();

        $result = [];

        foreach ($fields as $field) {
            $columnName = $this->nameConverter->toColumnName($field->name);

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
     * @return IndexDiff[]
     * @throws DBALException
     */
    public function compareTableIndexes(Entity $entity): array
    {
        $tableName = $this->nameConverter->tableName($entity);

        if (!$this->tableExists($tableName)) throw new LogicException("Table $tableName does not exist");
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

            if ($dbIndex->getColumns() != array_map([$this->nameConverter, 'toColumnName'], $index->columns)) {
                $result[] = $diff;
                continue;
            }
        }

        foreach ($indexes as $index) {
            $result[] = new IndexDiff($index);
        }

        return $result;
    }

    /**
     * @param Entity $entity
     * @return RelationDiff[]
     * @throws DBALException
     */
    public function compareTableRelations(Entity $entity): array
    {
        $tableName = $this->nameConverter->tableName($entity);

        if (!$this->tableExists($tableName)) throw new LogicException("Table $tableName does not exist");
        $foreignKeys = $this->schemaManager->listTableForeignKeys($tableName);

        /** @var Relation[] $relations */
        $relations = array_filter($entity->relations(), function (Relation $relation) { return $relation->type == Relation::MANY_TO_ONE; });

        $result = [];
        foreach ($foreignKeys as $foreignKey) {
            $name = $foreignKey->getName();
            $relationName = null;
            if (strpos($name, $entity->name) === 0) {
                $relationName = substr($name, strlen($entity->name));
            }

            $diff = new RelationDiff();
            $diff->foreignKey = $foreignKey;

            if (!$relationName || !isset($relations[$relationName])) {
                $diff->operation = RelationDiff::OP_REMOVE;
                $result[] = $diff;
                continue;
            }

            $relation = $relations[$relationName];
            unset($relations[$relationName]);
            $diff->relation = $relation;
            $diff->operation = RelationDiff::OP_CHANGE;

            if ($foreignKey->getForeignTableName() != $this->nameConverter->tableName($relation->entity)) {
                $result[] = $diff;
                continue;
            }

            $fkColumns = $foreignKey->getLocalColumns();
            if (count($fkColumns) > 1 || $fkColumns[0] != $this->nameConverter->toColumnName($relation->field())) {
                $result[] = $diff;
                continue;
            }
        }

        foreach ($relations as $relation) {
            $result[] = new RelationDiff($relation);
        }

        return $result;
    }
}
