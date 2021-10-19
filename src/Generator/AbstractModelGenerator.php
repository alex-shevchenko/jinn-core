<?php


namespace Jinn\Generator;


use Jinn\Definition\Models\ApiController;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;

abstract class AbstractModelGenerator
{
    /**
     * @param Entity[] $entities
     */
    public function generate(Application $application): void {
        $entities = $application->entities();

        foreach ($entities as $entity) {
            if (!$entity->noModel) {
                $this->generateModel($entity);
            }
        }
        $this->generateMigrations($entities);

        $this->generateApiControllers($application);
    }

    abstract protected function generateModel(Entity $entity): void;

    /**
     * @param Entity[] $entities
     */
    abstract protected function generateMigrations(array $entities): void;

    abstract protected function generateApiControllers(Application $application): void;
}
