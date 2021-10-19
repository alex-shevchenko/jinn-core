<?php


namespace Jinn\Definition\Processors;


use Jinn\Definition\DefinitionProcessorInterface;
use Jinn\Definition\Models\ApiController;
use Jinn\Definition\Models\ApiMethod;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;
use Jinn\Definition\Models\Index;
use Jinn\Definition\Models\Policy;
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
                    $apiController->addMethod(new ApiMethod($name));
                } elseif (is_string($methodDefinition)) {
                    $apiController->addMethod(new ApiMethod($name, $methodDefinition));
                } else {
                    $method = new ApiMethod($name, $methodDefinition['type'] ?? $name);
                    $method->fields = $methodDefinition['fields'] ?? null;
                    $apiController->addMethod($method);

                    $method->relation = $methodDefinition['relation'] ?? null;

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
