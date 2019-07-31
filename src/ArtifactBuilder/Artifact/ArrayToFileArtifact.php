<?php namespace ILIAS\ArtifactBuilder\Artifact;

/**
 * Class ArrayToFileArtifact
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ArrayToFileArtifact implements Artifact
{

    /**
     * @var array
     */
    private $array_data = [];
    /**
     * @var string
     */
    private $file_name = "";
    /**
     * @var string
     */
    private $base_path = "";


    /**
     * ArrayToFileArtifact constructor.
     *
     * @param string $base_path
     * @param string $file_name
     * @param array  $class_names
     */
    public function __construct(string $base_path, string $file_name, array $class_names)
    {
        $this->base_path = $base_path;
        $this->file_name = $file_name;
        $this->array_data = $class_names;
    }


    public final function save() : void
    {
        $root = substr(__FILE__, 0, strpos(__FILE__, "/src"));

        $full_file_path = rtrim($root . "/" . $this->base_path, "/") . "/" . $this->file_name . ".php";
        file_put_contents($full_file_path, "<?php return " . var_export($this->getArrayData(), true) . ";");
    }


    /**
     * @return string[]
     */
    protected function getArrayData() : array
    {
        return $this->array_data;
    }
}
