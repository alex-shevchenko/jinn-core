<?php

namespace Jinn;

use Jinn\Models\Application;
use Jinn\Models\Entity;
use Jinn\Models\Field;
use Jinn\Models\Index;
use Jinn\Models\Relation;
use Jinn\Models\Types;
use Symfony\Component\Yaml\Yaml;

class JinnDefinitionReader
{
    public function read(string $file): Application {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("File or folder " . $file . " does not exist");
        }

        $application = new Application();

        if (is_dir($file)) {
            $this->readDir($application, $file);
        } else {
            $this->readFile($application, $file);
        }

        $this->processRelations($application);

        return $application;
    }

    protected function readDir(Application $application, string $folder): void {
        $dir = opendir($folder);

        while ($file = readdir($dir)) {
            $file = $folder . '/' . $file;
            if (is_dir($file)) continue;

            $this->readFile($application, $file);
        }

    }

    protected function readFile(Application $application, string $file): void {
        $defs = Yaml::parseFile($file);
        foreach ($defs as $name => $def) {
            if (!is_array($def)) throw new \LogicException("Definition for entity $name must be an object");
            $this->processEntity($application, $name, $def);
        }
    }

    protected static function idField(): Field {
        $field = new Field();
        $field->name = 'id';
        $field->primary = true;
        $field->type = Types::BIGINT;

        return $field;
    }

    protected function processEntity(Application $application, string $name, array $def): void {
        $entity = new Entity();
        $entity->name = $name;

        $entity->addField($this::idField());

        if ($def['fields']) {
            foreach ($def['fields'] as $fieldName => $fieldDef) {
                $this->processField($entity, $fieldName, $fieldDef);
            }
        }

        if (isset($def['indexes'])) {
            foreach ($def['indexes'] as $indexName => $indexDef) {
                $this->processIndex($entity, $indexName, $indexDef);
            }
        }

        $application->addEntity($entity);
    }

    /**
     * @param Entity $entity
     * @param string $fieldName
     * @param array|string $fieldDef
     */
    protected function processField(Entity $entity, string $fieldName, $fieldDef) {
        $field = new Field();
        $field->name = $fieldName;

        if (is_array($fieldDef)) {
            if (isset($fieldDef['type'])) {
                $field->type = $fieldDef['type'];
                $field->default = $fieldDef['default'] ?? null;
                $field->length = $fieldDef['length'] ?? 0;
                $field->required = $fieldDef['required'] ?? true;

                $isUnique = $fieldDef['unique'] ?? false;
                $isIndex = $isUnique || ($fieldDef['index'] ?? false);

                if ($isIndex) {
                    $index = new Index();
                    $index->name = $fieldName;
                    $index->columns = [$fieldName];
                    $index->isUnique = $isUnique;
                    $entity->addIndex($index);
                }

                $entity->addField($field);
            } elseif (isset($fieldDef['relation'])) {
                $relationDef = $fieldDef;
                $relation = new Relation();
                $relation->name = $fieldName;

                if (!isset($relationDef['entity']) || !isset($relationDef['relation'])) throw new \LogicException("Relation $fieldName of entity {$entity->name} must have a related entity and type");
                $relation->entityName = $relationDef['entity'];
                $relation->type = $relationDef['relation'];
                $relation->field = $relationDef['field'] ?? null;

                $entity->addRelation($relation);
            } else {
                throw new \LogicException("Field $fieldName of entity {$entity->name} must have a type or relation");
            }
        } else {
            $field->type = $fieldDef;
            $entity->addField($field);
        }

    }

    protected function processIndex(Entity $entity, string $indexName, array $indexDef) {
        $index = new Index();
        $index->name = $indexName;

        if (isset($indexDef['columns'])) {
            $index->columns = $indexDef['columns'];
            $index->isUnique = $indexDef['isUnique'] ?? false;
        } else {
            $index->columns = $indexDef;
        }

        $entity->addIndex($index);
    }

    protected function processRelations(Application $application) {
        $entities = $application->entities();
        foreach ($entities as $entity) {
            foreach ($entity->relations() as $relation) {
                $relatedEntity = $application->entity($relation->entityName);
                $relation->entity = $relatedEntity;

                if ($relation->type == Relation::MANY_TO_MANY) {
                    $pivotEntityName = $relation->field;
                    if (!$pivotEntityName) {
                        $names = [$entity->name, $relatedEntity->name];
                        sort($names);
                        $pivotEntityName = implode('', $names);
                    }

                    if (!$application->hasEntity($pivotEntityName)) {
                        $pivotEntity = new Entity();
                        $pivotEntity->name = $pivotEntityName;
                        $fromFieldName = $entity->name . 'Id';
                        $toFieldName = $relatedEntity->name . 'Id';
                        $pivotEntity->addField(new Field($fromFieldName, Types::BIGINT));
                        $pivotEntity->addField(new Field($toFieldName, Types::BIGINT));
                        $pivotEntity->addIndex(new Index($pivotEntityName, [$fromFieldName, $toFieldName], true));
                        $pivotEntity->addRelation(new Relation($entity, Relation::MANY_TO_ONE));
                        $pivotEntity->addRelation(new Relation($relatedEntity, Relation::MANY_TO_ONE));
                        $pivotEntity->noModel = true;
                        $pivotEntity->isPivot = true;

                        $application->addEntity($pivotEntity);
                    }
                } else {
                    if ($relation->type == Relation::ONE_TO_MANY) {
                        $oneEntity = $entity;
                        $manyEntity = $relatedEntity;
                    } else if ($relation->type == Relation::MANY_TO_ONE) {
                        $manyEntity = $relatedEntity;
                        $oneEntity = $entity;
                    } else {
                        throw new \LogicException("Invalid relation type {$relation->type}");
                    }

                    $relationFieldName = $relation->field ?? ($oneEntity->name . 'Id');

                    if (!$manyEntity->hasField($relationFieldName)) {
                        $relationField = new Field($relationFieldName, Types::BIGINT);
                        $relationField->noModel = true;
                        $manyEntity->addField($relationField);
                    }
                }
            }
        }
    }
}
