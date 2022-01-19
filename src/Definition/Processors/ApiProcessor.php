<?php


namespace Jinn\Definition\Processors;


use Jinn\Definition\DefinitionProcessorInterface;
use Jinn\Definition\Models\ApiController;
use Jinn\Definition\Models\ApiMethod;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;
use Jinn\Definition\Models\Index;
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

                    if (isset($methodDefinition['fields']) && isset($methodDefinition['view']))
                        throw new LogicException("Api method $name cannot have both view and fields defined");

                    $method->relation = $methodDefinition['relation'] ?? null;

                    if (isset($methodDefinition['view'])) {
                        if (strpos($methodDefinition['view'], '.') !== false) {
                            $method->viewName = $methodDefinition['view'];
                        } else {
                            $method->view = $entity->view($methodDefinition['view']);
                        }
                    } else {
                        if ($method->relation && !isset($methodDefinition['fields'])) throw new \InvalidArgumentException("View or fields must be defined for the relation method $name");
                        $method->view = new View($entity->name, $name, $methodDefinition['fields'] ?? $entity->allFields());
                    }

                    $apiController->addMethod($method);

                    $method->authRequired = $methodDefinition['auth'] ?? false;

                    $method->route = $methodDefinition['route'] ?? null;

                    if (isset($methodDefinition['policy'])) {
                        $rules = $methodDefinition['policy'];

                        $policy = new Policy($name);
                        $policy->owner = $rules['owner'] ?? null;
                        $policy->anonymous = $rules['anonymous'] ?? false;
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
