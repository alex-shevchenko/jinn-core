<?php


namespace Jinn\Generator;

class GeneratorFactory
{
    const MODEL = 'model';
    const VIEW = 'view';
    const API_CONTROLLER = 'apiController';

    private GeneratorConfig $config;
    /**
     * @var ClassGenerator[]
     */
    private static array $generators = [];
    private static string $namespace;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
    }

    public static function setNamespace($namespace)
    {
        self::$namespace = $namespace;
    }

    public function get(string $type): ClassGenerator
    {
        if (!isset(self::$generators[$type])) {
            $className = '\\' . self::$namespace . '\\' . ucfirst($type) . 'Generator';
            $namespace = $this->config->{$type . 'Namespace'};
            self::$generators[$type] = new $className($this, $this->config, $namespace);
        }

        return self::$generators[$type];
    }
}
