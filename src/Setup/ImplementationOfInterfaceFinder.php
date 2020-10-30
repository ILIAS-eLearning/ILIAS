<?php

namespace ILIAS\Setup;

/**
 * Class ImplementationOfInterfaceFinder
 *
 * @package ILIAS\ArtifactBuilder\Generators
 */
class ImplementationOfInterfaceFinder
{
    /**
     * @var string
     */
    protected $root;

    /**
     * @var string[]
     */
    protected $ignore = [
        '.*/src/',
        '.*/libs/',
        '.*/test/',
        '.*/tests/',
        '.*/setup/',
        // Classes using removed Auth-class from PEAR
        '.*ilSOAPAuth.*',
        // Classes using unknown
        '.*ilPDExternalFeedBlockGUI.*',
    ];

    /**
     * @var string[]|null
     */
    protected $classmap = null;

    public function __construct()
    {
        $this->root = substr(__FILE__, 0, strpos(__FILE__, "/src"));
        $this->classmap = include "./libs/composer/vendor/composer/autoload_classmap.php";
    }

    /**
     * The matcher finds the class names implementing the given interface, while
     * ignoring paths in self::$ignore and and the additional patterns provided.
     *
     * Patterns are regexps (without delimiters) to define complete paths on the
     * filesystem to be ignored.
     *
     * @param   string[] $additional_ignore
     */
    public function getMatchingClassNames(string $interface, array $additional_ignore = []) : \Iterator
    {
        foreach ($this->getAllClassNames($additional_ignore) as $class_name) {
            try {
                $r = new \ReflectionClass($class_name);
                if ($r->isInstantiable() && $r->implementsInterface($interface)) {
                    yield $class_name;
                }
            } catch (\Throwable $e) {
                // noting to do here
            }
        }
    }

    /**
     * @param   string[] $additional_ignore
     */
    protected function getAllClassNames(array $additional_ignore) : \Iterator
    {
        $ignore = array_merge($this->ignore, $additional_ignore);

        if (!is_array($this->classmap)) {
            throw new \LogicException("Composer ClassMap not loaded");
        }

        $regexp = implode(
            "|",
            array_map(
                // fix path-separators to respect windows' backspaces.
                function ($v) {
                    return "(" . str_replace('/', '(/|\\\\)', $v) . ")";
                },
                $ignore
            )
        );


        foreach ($this->classmap as $class_name => $file_path) {
            $path = str_replace($this->root, "", realpath($file_path));
            if (!preg_match("#^" . $regexp . "$#", $path)) {
                yield $class_name;
            }
        }
    }
}
