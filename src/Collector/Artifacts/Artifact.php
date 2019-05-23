<?php namespace ILIAS\Collector\Artifacts;

/**
 * Class Artifact
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Artifact {

	public function save(): void;
}
