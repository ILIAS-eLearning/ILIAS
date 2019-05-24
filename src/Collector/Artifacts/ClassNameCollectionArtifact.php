<?php namespace ILIAS\Collector\Artifacts;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class ClassNameCollectionArtifact
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ClassNameCollectionArtifact implements Artifact
{

    /**
     * @var array
     */
    private $class_names;
    /**
     * @var string
     */
    private $file_name = "";
    /**
     * @var string
     */
    private $base = "libs/ilias/Artifacts/BootLoader";


    /**
     * ClassNameCollectionArtifact constructor.
     *
     * @param string $file_name
     * @param array  $class_names
     */
    public function __construct(string $file_name, array $class_names)
    {
        $this->file_name = $file_name;
        $this->class_names = $class_names;
    }


    public final function save() : void
    {
        $root = substr(__FILE__, 0, strpos(__FILE__, "/src"));
        $a = new Local($root . "/" . $this->base);
        $f = new Filesystem($a);
        $f->put($this->file_name . ".php", "<?php return " . var_export($this->getClassNames(), true) . ";");
    }


    /**
     * @return string[]
     */
    protected function getClassNames() : array
    {
        return $this->class_names;
    }
}
