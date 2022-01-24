<?php


namespace Jinn\Definition\Processors;


use Jinn\Definition\DefinitionProcessorInterface;
use Jinn\Definition\Models\ApiController;
use Jinn\Definition\Models\ApiMethod;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;
use Jinn\Definition\Models\Policy;
use Jinn\Definition\Models\View;
use LogicException;

class ApiProcessor implements DefinitionProcessorInterface
{
    public function processDefinition(Application $application, Entity $entity, $definition)
    {
        $apiController = new ApiController($entity);

        if (is_null($definition)) {
            $apiController->fillDefault();
        } else {
            foreach ($definition as $name => $methodDefinition) {
                if ($methodDefinition === false) {
                    if (!$apiController->hasMethods()) $apiController->fillDefault();
                    $apiController->removeMethod($name);
                } elseif (is_null($methodDefinition)) {
                    $apiController->addMethod(new ApiMethod($name, null, new View($entity->name, $name, $entity->allFields())));
                } elseif (is_string($methodDefinition)) {
                    $apiController->addMethod(new ApiMethod($name, $methodDefinition, new View($entity->name, $name, $entity->allFields())));
                } else {
                    $method = new ApiMethod($name, $methodDefinition['type'] ?? $name);

                    if (isset($methodDefinition['properties']) && isset($methodDefinition['view']))
                        throw new LogicException("Api method $name cannot have both view and properties defined");

                    if ($method->type == ApiMethod::RELATED_LIST) {
                        $method->relation = $methodDefinition['relation'] ?? $name;
                    }

                    $method->viewName = $methodDefinition['view'] ?? null;
                    $method->properties = $methodDefinition['properties'] ?? null;

                    $apiController->addMethod($method);

                    $method->auth = $methodDefinition['auth'] ?? false;

                    $method->route = $methodDefinition['route'] ?? null;

                    if (isset($methodDefinition['policy'])) {
                        $rules = $methodDefinition['policy'];

                        $policy = new Policy($name);
                        $policy->owner = $rules['owner'] ?? null;
                        if (isset($rules['role'])) $policy->roles = [$rules['role']];
                        else if (isset($rules['roles'])) $policy->roles = $rules['roles'];

                        $method->policy = $policy;
                    }
                }
            }
        }

        $application->addApiController($apiController);
    }
}
