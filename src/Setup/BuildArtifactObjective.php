<?php


/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de>, Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * This is an objective to build some artifact.
 */
abstract class BuildArtifactObjective implements Objective
{
	/**
	 * Get the filename where the builder wants to put its artifact.
	 *
	 * This is understood to be a path relative to the ILIAS root directory.
	 */
	abstract public function getArtifactPath() : string;

	/**
	 * Build the artifact based. If you want to use the environment
	 * reimplement `buildIn` instead.
	 */
	abstract public function build() : Artifact;

	/**
	 * Builds an artifact in some given Environment.
	 *
	 * Defaults to just dropping the environment and using `build`.
	 *
	 * If you want to reimplement this, you most probably also want to reimplement
	 * `getPreconditions` to prepare the environment properly.
	 */
	public function buildIn(Environment $env) : Artifact {
		return $this->build();
	}

	/**
	 * Defaults to no preconditions.
	 *
	 * @inheritdocs
	 */
	public function getPreconditions(Environment $environment) : array {
		return [];
	}

	/**
	 * Uses hashed Path.
	 *
	 * @inheritdocs
	 */
	public function getHash() : string {
		return hash("sha256", $this->getArtifactPath());
	}

	/**
	 * Defaults to "Build $this->getArtifactPath()".
	 *
	 * @inheritdocs
	 */
	public function getLabel() : string {
		return 'Build '.$this->getArtifactPath();
	}

	/**
	 * Defaults to 'true'.
	 *
	 * @inheritdocs
	 */
	public function isNotable() : bool {
		return true;
	}

	/**
	 * Builds the artifact and puts it in its location.
	 *
	 * @inheritdocs
	 */
	public function achieve(Environment $environment) : Environment {
		$artifact = $this->buildIn($environment);

		// TODO: Do we want to configure this?
		$base_path = getcwd();
		$path = $base_path."/".$this->getArtifactPath();

		$this->makeDirectoryFor($path);

		file_put_contents($path, $artifact->serialize());

		return $environment;
	}

	protected function makeDirectoryFor(string $path) : void {
		$dir = pathinfo($path)["dirname"];
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}
	}
}
