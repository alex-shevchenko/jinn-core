<?php


namespace Jinn\Generator;

class GeneratorConfig
{
    public string $appNamespace;
    public string $appFolder;
    public string $generatedNamespace;
    public string $generatedFolder;

    public string $modelNamespace;
    public string $apiControllerNamespace;
    public string $viewNamespace;

    public string $migrationsPath;

    /**
     * @var callable
     */
    public $output;
}
