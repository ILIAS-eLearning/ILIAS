<?php namespace ILIAS\GlobalScreen\BootLoader;

use Closure;
use ILIAS\ArtifactBuilder\Generators\GeneratorFactory;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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
     * @param string $in_path
     */
    public function __construct(string $interface, string $in_path)
    {
        $this->interface = $interface;
        $this->path = $in_path;
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

        $f = new GeneratorFactory();

        $directory_iterator = new RecursiveDirectoryIterator($root . "/" . $directory);
        $iterator_iterator = new RecursiveIteratorIterator($directory_iterator);

        $file_endings = $f->splFileInfoByFileEnding('php');
        $filtered_php_files = $file_endings($iterator_iterator);

        $implements_interface = $f->classNamesWhichImplementInterface($interface);
        $classes_which_implement_interface = $implements_interface($filtered_php_files);

        $class_names = [];

        foreach ($classes_which_implement_interface as $class_name) {
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

}