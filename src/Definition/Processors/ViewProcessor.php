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

class ViewProcessor implements DefinitionProcessorInterface
{
    public function processDefinition(Application $application, Entity $entity, $definition)
    {
        if ($definition) {
            foreach ($definition as $name => $viewDefinition) {
                $view = new View($entity->name, $name);

                if (is_null($viewDefinition)) {
                    $view->fields = $entity->allFields();
                } elseif (isset($viewDefinition[0])) {
                    $view->fields = $viewDefinition;
                } else {
                    if (!isset($viewDefinition['extends'])) throw new \InvalidArgumentException("View $name must either define list of fields or extend another view");

                    $extends = $viewDefinition['extends'];
                    $fields = $entity->view($extends)->fields;

                    if (isset($viewDefinition['add'])) {
                        $fields = array_merge($fields, $viewDefinition['add']);
                    }
                    if (isset($viewDefinition['remove'])) {
                        $fields = array_diff($fields, $viewDefinition['remove']);
                    }
                    $view->fields = $fields;
                }

                $entity->addView($view);
            }
        }
    }
}
