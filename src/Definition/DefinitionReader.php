<?php

namespace Jinn\Definition;
use Jinn\Definition\Models\Application;
use Jinn\Definition\Models\Entity;
use Jinn\Definition\Processors\ApiProcessor;
use Jinn\Definition\Processors\ClassProcessor;
use Jinn\Definition\Processors\FieldsProcessor;
use Jinn\Definition\Processors\IndexesProcessor;
use Jinn\Definition\Processors\PolicyProcessor;
use Jinn\Definition\Processors\RelationsPostProcessor;
use Symfony\Component\Yaml\Yaml;
use LogicException;
use InvalidArgumentException;

class DefinitionReader
{
    /** @var DefinitionProcessorInterface[] */
    private array $processors = [];
    /** @var DefinitionPostProcessorInterface[] */
    private array $postProcessors = [];

    public function __construct()
    {
        $this->processors['fields'] = new FieldsProcessor();
        $this->processors['indexes'] = new IndexesProcessor();
        $this->processors['api'] = new ApiProcessor();
        $this->processors['class'] = new ClassProcessor();

        $this->postProcessors[] = new RelationsPostProcessor();
    }

    public function read(string $file): Application {
        if (!file_exists($file)) {
            throw new InvalidArgumentException("File or folder " . $file . " does not exist");
        }

        $application = new Application();

        if (is_dir($file)) {
            $this->readDir($application, $file);
        } else {
            $this->readFile($application, $file);
        }

        foreach ($this->postProcessors as $postProcessor) {
            $postProcessor->process($application);
        }

        return $application;
    }

    protected function readDir(Application $application, string $folder): void {
        $dir = opendir($folder);

        while ($file = readdir($dir)) {
            $file = $folder . '/' . $file;
            if (is_dir($file)) continue;

            $this->readFile($application, $file);
        }

    }

    protected function readFile(Application $application, string $file): void {
        $defs = Yaml::parseFile($file);
        foreach ($defs as $name => $def) {
            if (!is_array($def)) throw new LogicException("Definition for entity $name must be an object");
            $this->processEntity($application, $name, $def);
        }
    }

    protected function processEntity(Application $application, string $name, array $def): void {
        if (!$application->hasEntity($name)) {
            $entity = new Entity();
            $entity->name = $name;
            $application->addEntity($entity);
        }
        $entity = $application->entity($name);

        foreach ($def as $key => $definition) {
            if (!isset($this->processors[$key])) throw new LogicException("No processor found for $key definition");
            $this->processors[$key]->processDefinition($application, $entity, $definition);
        }

    }
}

