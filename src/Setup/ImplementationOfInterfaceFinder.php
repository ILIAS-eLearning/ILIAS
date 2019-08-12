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
    private $interface = "";
    /**
     * @var array
     */
    private $ignore = [
        '/libs/',
        '/test/',
        '/tests/',
        '/setup/',
        // Classes using removed Auth-class from PEAR
        '.*ilAuthCalendar.*',
        '.*ilAuthCAS.*',
        '.*ilAuthContainerCAS.*',
        '.*ilAuthContainerECS.*',
        '.*ilAuthContainerSOAP.*',
        '.*ilAuthECS.*',
        '.*ilAuthHTTP.*',
        '.*ilAuthInactive.*',
        '.*ilAuthLogObserver.*',
        '.*ilAuthSOAP.*',
        '.*ilCASAuth.*',
        '.*ilSOAPAuth.*',
        // Classes using unknown 
        '.*ilPDExternalFeedBlockGUI.*'
    ];


    public function __construct(string $interface)
    {
        $this->interface = $interface;
        $this->getAllClassNames();
    }


    private function getAllClassNames() : \Iterator
    {
        // We use the composer classmap ATM
        $composer_classmap = include "./libs/composer/vendor/composer/autoload_classmap.php";
        $root = substr(__FILE__, 0, strpos(__FILE__, "/src"));

        if (!is_array($composer_classmap)) {
            throw new \LogicException("Composer ClassMap not loaded");
        }

		$regexp = implode(
			"|",
			array_map(
				function($v) { return "($v)"; },
				$this->ignore
			)
		); 

		echo $regexp."\n";

        foreach ($composer_classmap as $class_name => $file_path) {
            $path = str_replace($root, "", realpath($file_path));
            if (!preg_match("#^" . $regexp . "$#", $path)) {
				echo $path." => ".$class_name."\n";
                yield $class_name;
            }
        }
    }

    public function getMatchingClassNames() : \Iterator
    {
        foreach ($this->getAllClassNames() as $class_name) {
            try {
                $r = new \ReflectionClass($class_name);
                if ($r->isInstantiable() && $r->implementsInterface($this->interface)) {
                    yield $class_name;
                }
            } catch (\Throwable $e) {
                // noting to do here
            }
        }
    }
}
