<?php


namespace Jinn\Generator;


use Jinn\Definition\Models\ApiController;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;
use Jinn\Definition\Models\View;

abstract class AbstractEntityGenerator
{
    public function generate(Application $application, $generateMigrations = true): void {
        $entities = $application->entities();

        foreach ($entities as $entity) {
            if (!$entity->noModel) {
                $this->generateModel($entity);
                $this->generateViews($entity);
            }
        }

        if ($generateMigrations)
            $this->generateMigrations($entities);

        $this->generateApiControllers($application);
    }

    protected function generateViews(Entity $entity): void
    {
        foreach ($entity->views() as $view)
        {
            $this->generateView($entity, $view);
        }
    }

    abstract protected function generateView(Entity $entity, View $view): void;

    abstract protected function generateModel(Entity $entity): void;

    /**
     * @param Entity[] $entities
     */
    abstract protected function generateMigrations(array $entities): void;

    abstract protected function generateApiControllers(Application $application): void;
}
