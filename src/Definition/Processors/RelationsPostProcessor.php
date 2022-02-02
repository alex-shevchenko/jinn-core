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
                    $pivotEntityName = $relation->via;
                    if (!$pivotEntityName) {
                        $names = [$entity->name, $relatedEntity->name];
                        sort($names);
                        $pivotEntityName = implode('', $names);
                    }

                    if (!$application->hasEntity($pivotEntityName)) {
                        $pivotEntity = new Entity();
                        $pivotEntity->name = $pivotEntityName;
                        $application->addEntity($pivotEntity);
                        $pivotEntity->noModel = true;
                    } else {
                        $pivotEntity = $application->entity($pivotEntityName);
                    }

                    $fromFieldName = lcfirst($entity->name) . 'Id';
                    $toFieldName = lcfirst($relatedEntity->name) . 'Id';
                    $pivotEntity->addField(new Field($fromFieldName, Types::BIGINT));
                    $pivotEntity->addField(new Field($toFieldName, Types::BIGINT));
                    $pivotEntity->addIndex(new Index($pivotEntityName, [$fromFieldName, $toFieldName], true));
                    $pivotEntity->addRelation(new Relation($entity, Relation::MANY_TO_ONE));
                    $pivotEntity->addRelation(new Relation($relatedEntity, Relation::MANY_TO_ONE));
                    $pivotEntity->isPivot = true;
                } else {
                    if ($relation->type == Relation::ONE_TO_MANY) {
                        $oneEntity = $entity;
                        $manyEntity = $relatedEntity;
                    } else if ($relation->type == Relation::MANY_TO_ONE) {
                        $manyEntity = $entity;
                        $oneEntity = $relatedEntity;
                    } else {
                        throw new LogicException("Invalid relation type {$relation->type}");
                    }

                    $relationName = $relation->via ?? lcfirst($oneEntity->name);
                    $relationFieldName = $relation->field();

                    if ($relation->type == Relation::ONE_TO_MANY) {
                        if (!$manyEntity->hasRelation($relationName)) {
                            $reverseRelation = new Relation($oneEntity, Relation::MANY_TO_ONE, $relationName);
                            $reverseRelation->noModel = true;
                            $reverseRelation->via = $relation->via;
                            $manyEntity->addRelation($reverseRelation);
                        } else {
                            $reverseRelation = $manyEntity->relation($relationName);
                        }
                        $relationFieldName = $reverseRelation->field();
                    }
                    if (!$manyEntity->hasField($relationFieldName)) {
                        $relationField = new Field($relationFieldName, Types::BIGINT);
                        $relationField->required = false;
                        $relationField->noModel = true;
                        $manyEntity->addField($relationField);
                    }
                }
            }
        }
    }
}
