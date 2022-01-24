<?php


namespace Jinn\Definition\Processors;


use Jinn\Definition\DefinitionPostProcessorInterface;
use Jinn\Definition\Models\ApiMethod;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\View;

class ViewsPostProcessor implements DefinitionPostProcessorInterface
{
    public function process(Application $application)
    {
        $apiControllers = $application->apiControllers();
        foreach ($apiControllers as $controller) {
            foreach ($controller->methods() as $method) {
                if ($method->type == ApiMethod::RELATED_LIST) {
                    $entity = $controller->entity->relation($method->relation)->entity;
                } else {
                    $entity = $controller->entity;
                }
                if ($method->viewName) {
                    $method->view = $entity->view($method->viewName);
                } else {
                    $method->view = new View($entity->name, $method->name, $method->properties ?? $entity->allFields());
                }
            }
        }
    }
}
