<?php


namespace Jinn\Definition\Processors;


use Jinn\Definition\DefinitionProcessorInterface;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;
use Jinn\Definition\Models\Field;
use Jinn\Definition\Models\Index;
use Jinn\Definition\Models\Relation;
use Jinn\Definition\Types;
use LogicException;

class FieldsProcessor implements DefinitionProcessorInterface
{
    public function processDefinition(Application $application, Entity $entity, $definition)
    {
        $entity->addField($this::idField());

        if ($definition) {
            foreach ($definition as $fieldName => $fieldDef) {
                $this->processField($entity, $fieldName, $fieldDef);
            }
        }
    }

    protected static function idField(): Field {
        $field = new Field();
        $field->name = 'id';
        $field->primary = true;
        $field->type = Types::BIGINT;

        return $field;
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

                if (!isset($relationDef['entity']) || !isset($relationDef['relation'])) throw new LogicException("Relation $fieldName of entity {$entity->name} must have a related entity and type");
                $relation->entityName = $relationDef['entity'];
                $relation->type = $relationDef['relation'];
                $relation->field = $relationDef['field'] ?? null;

                $entity->addRelation($relation);
            } else {
                throw new LogicException("Field $fieldName of entity {$entity->name} must have a type or relation");
            }
        } else {
            $field->type = $fieldDef;
            $entity->addField($field);
        }

    }
}
