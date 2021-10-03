<?php

namespace Jinn\Definition;

use Jinn\Definition\Models\Entity;

interface DefinitionProcessorInterface
{
    public function processDefinition(Entity $entity, array $definition);
}
