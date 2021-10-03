<?php


namespace Jinn\Generator;


use Jinn\Definition\Models\Entity;

abstract class AbstractModelGenerator
{
    /**
     * @param Entity[] $entities
     */
    public function generateEntities(array $entities): void {
        foreach ($entities as $entity) {
            if (!$entity->noModel) {
                $this->generateModel($entity);
            }
        }
        $this->generateMigrations($entities);
    }

    abstract protected function generateModel(Entity $entity): void;

    /**
     * @param Entity[] $entities
     */
    abstract protected function generateMigrations(array $entities): void;
}
