<?php namespace ILIAS\Collector;

use ILIAS\Collector\Artifacts\Artifact;
use ILIAS\Collector\IO\IOInterface;

/**
 * Interface EventHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface EventHandler {

	/**
	 * @return IOInterface
	 */
	public function IO(): IOInterface;


	public function run(): void;


	/**
	 * @return Artifact
	 */
	public function getArtifact(): Artifact;
}
