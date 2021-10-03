<?php


namespace Jinn\Definition;


use Jinn\Definition\Models\Application;

interface DefinitionPostProcessorInterface
{
    public function process(Application $application);
}
