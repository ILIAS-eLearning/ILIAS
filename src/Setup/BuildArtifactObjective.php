<?php


/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de>, Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * This is an objective to build some artifact.
 */
abstract class BuildArtifactObjective implements Objective
{
	/**
	 * Build the artifact based. If you want to use the environment
	 * reimplement `buildIn` instead.
	 */
	abstract public function build() : Artifact;

	/**
	 * Get the filename where the builder wants to put its artifact.
	 *
	 * This is understood to be an absolute path.
	 */
	abstract public function getArtifactPath() : string;

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
		file_put_contents($this->getArtifactPath(), $artifact->serialize());
		return $environment;
	}
}
