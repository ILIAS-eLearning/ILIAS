<?php namespace ILIAS\Collector\Artifacts;

/**
 * Class AbstractClassNameCollectionArtifact
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractClassNameCollectionArtifact implements Artifact {

	/**
	 * @var string
	 */
	private $file_name = "";
	/**
	 * @var string
	 */
	private $base = "libs/ilias/Artifacts/ClassLoader";


	/**
	 * AbstractClassNameCollectionArtifact constructor.
	 *
	 * @param string $file_name
	 */
	public function __construct(string $file_name) { $this->file_name = $file_name; }


	public final function save(): void {
		file_put_contents("{$this->base}/{$this->file_name}.php", "<?php return " . var_export($this->getClassNames(), true) . ";");
	}


	/**
	 * @return string[]
	 */
	abstract protected function getClassNames(): array;
}
