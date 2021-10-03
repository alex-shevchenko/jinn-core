<?php


namespace Jinn\Definition\Processors;


use Jinn\Definition\DefinitionPostProcessorInterface;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;
use Jinn\Definition\Models\Field;
use Jinn\Definition\Models\Index;
use Jinn\Definition\Models\Relation;
use Jinn\Definition\Types;
use LogicException;

class RelationsPostProcessor implements DefinitionPostProcessorInterface
{
    public function process(Application $application)
    {
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
                        throw new LogicException("Invalid relation type {$relation->type}");
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
