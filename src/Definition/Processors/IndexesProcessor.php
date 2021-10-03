<?php


namespace Jinn\Definition\Processors;


use Jinn\Definition\DefinitionProcessorInterface;
use Jinn\Definition\Models\Entity;
use Jinn\Definition\Models\Index;

class IndexesProcessor implements DefinitionProcessorInterface
{
    public function processDefinition(Entity $entity, array $definition)
    {
        foreach ($definition as $indexName => $indexDef) {
            $this->processIndex($entity, $indexName, $indexDef);
        }
    }

    protected function processIndex(Entity $entity, string $indexName, array $indexDef) {
        $index = new Index();
        $index->name = $indexName;

        if (isset($indexDef['columns'])) {
            $index->columns = $indexDef['columns'];
            $index->isUnique = $indexDef['isUnique'] ?? false;
        } else {
            $index->columns = $indexDef;
        }

        $entity->addIndex($index);
    }
}
