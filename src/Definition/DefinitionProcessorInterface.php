<?php

namespace Jinn\Definition;

use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;

interface DefinitionProcessorInterface
{
    public function processDefinition(Application $application, Entity $entity, $definition);
}
