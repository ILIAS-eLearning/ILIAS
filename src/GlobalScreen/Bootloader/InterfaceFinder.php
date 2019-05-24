<?php namespace ILIAS\GlobalScreen\BootLoader;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Throwable;

/**
 * Class InterfaceFinder
 *
 * @package ILIAS\GlobalScreen\BootLoader
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class InterfaceFinder
{

    const DEBUG = false;
    /**
     * @var string
     */
    private $current_dir;
    /**
     * @var string
     */
    private $interface = "string";
    /**
     * @var string
     */
    private $path = "";


    /**
     * InterfaceFinder constructor.
     *
     * @param string $interface
     * @param string $path
     */
    public function __construct(string $interface, string $path)
    {
        $this->interface = $interface;
        $this->path = $path;
    }


    /**
     * @return string[]
     */
    public function getMatchingClassNames() : array
    {
        $interface = $this->interface;
        $directory = $this->path;
        $root = $this->initRootDirectory();
        require_once('./libs/composer/vendor/autoload.php');

        $directory_iterator = $this->getRecursiveDirectoryIterator($root . "/" . $directory);
        $filter = $this->getPHPFileFilter($directory_iterator);

        $iterator = new RecursiveIteratorIterator($filter);
        $f = $this->getInteraceGenerator($interface);

        $class_names = [];

        foreach ($f($iterator) as $class_name) {
            if ($class_name) {
                $class_names[] = $class_name;
            }
        }

        $this->restoreDirectory();

        return $class_names;
    }


    /**
     * @return bool|string
     */
    private function initRootDirectory()
    {
        $this->current_dir = getcwd();
        $root = substr(__FILE__, 0, strpos(__FILE__, "/src"));
        chdir($root);

        return $root;
    }


    private function restoreDirectory()
    {
        chdir($this->current_dir);
    }


    /**
     * @param string $directory
     *
     * @return RecursiveDirectoryIterator
     */
    private function getRecursiveDirectoryIterator(string $directory) : RecursiveDirectoryIterator
    {
        $directory_iterator = new RecursiveDirectoryIterator($directory);

        return $directory_iterator;
    }


    /**
     * @param RecursiveDirectoryIterator $directory_iterator
     *
     * @return \RecursiveCallbackFilterIterator
     */
    private function getPHPFileFilter(RecursiveDirectoryIterator $directory_iterator) : \RecursiveCallbackFilterIterator
    {
        $filter = new \RecursiveCallbackFilterIterator(
            $directory_iterator, function (\SplFileInfo $current, string $key, RecursiveDirectoryIterator $iterator) {
            if (strtolower($current->getExtension()) === "php" || $current->isDir() && !in_array($current->getFilename(), [".", ".."])) {
                return true;
            }

            return false;
        });

        return $filter;
    }


    /**
     * @param string $interface
     *
     * @return \Closure
     */
    private function getInteraceGenerator(string $interface) : \Closure
    {
        $f = function (RecursiveIteratorIterator $iterator) use ($interface) {
            foreach ($iterator as $file) {
                if (preg_match('/class\.(il.+)\.php$/i', $file->getFileName(), $matches)) {
                    $class_name = $matches[1];
                    try {
                        if (self::DEBUG === true) {
                            echo $class_name . PHP_EOL;
                        }
                        $r = new ReflectionClass($class_name);
                        if ($r->isInstantiable() && !$r->isAbstract()) {
                            if ($r->implementsInterface($interface)) {
                                yield $class_name;
                            }
                        }
                    } catch (Throwable $e) {
                        // nothing to do here
                    }
                }
            }
        };

        return $f;
    }
}