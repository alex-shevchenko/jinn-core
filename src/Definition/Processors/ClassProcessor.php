<?php


namespace Jinn\Definition\Processors;


use Jinn\Definition\DefinitionProcessorInterface;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;

class ClassProcessor implements DefinitionProcessorInterface
{
    public function processDefinition(Application $application, Entity $entity, $definition)
    {
        $entity->extends = $definition['extends'] ?? null;
        $entity->implements = $definition['implements'] ?? [];
        $entity->traits = $definition['traits'] ?? [];
    }
}
