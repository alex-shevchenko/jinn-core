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

class ViewsPostProcessor implements DefinitionPostProcessorInterface
{
    public function process(Application $application)
    {
        $apiControllers = $application->apiControllers();
        foreach ($apiControllers as $controller) {
            foreach ($controller->methods() as $method) {
                if (!$method->view && $method->viewName) {
                    list($entityName, $viewName) = explode('.', $method->viewName);
                    $method->view = $application->entity($entityName)->view($viewName);
                }
            }
        }
    }
}
