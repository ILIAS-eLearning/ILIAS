<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Setup\Artifact;

use ILIAS\Setup;

/**
 * This is an objective to build some artifact.
 */
abstract class BuildArtifactObjective implements Setup\Objective
{
    /**
     * Get the filename where the builder wants to put its artifact.
     *
     * This is understood to be a path relative to the ILIAS root directory.
     */
    abstract public function getArtifactPath(): string;

    /**
     * Build the artifact based. If you want to use the environment
     * reimplement `buildIn` instead.
     */
    abstract public function build(): Setup\Artifact;

    /**
     * Builds an artifact in some given Environment.
     *
     * Defaults to just dropping the environment and using `build`.
     *
     * If you want to reimplement this, you most probably also want to reimplement
     * `getPreconditions` to prepare the environment properly.
     */
    public function buildIn(Setup\Environment $env): Setup\Artifact
    {
        return $this->build();
    }

    /**
     * Defaults to no preconditions.
     *
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    /**
     * Uses hashed Path.
     *
     * @inheritdocs
     */
    public function getHash(): string
    {
        return hash("sha256", $this->getArtifactPath());
    }

    /**
     * Defaults to "Build $this->getArtifactPath()".
     *
     * @inheritdocs
     */
    public function getLabel(): string
    {
        return 'Build ' . $this->getArtifactPath();
    }

    /**
     * Defaults to 'true'.
     *
     * @inheritdocs
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * Builds the artifact and puts it in its location.
     *
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $artifact = $this->buildIn($environment);

        // TODO: Do we want to configure this?
        $base_path = getcwd();
        $path = $base_path . "/" . $this->getArtifactPath();

        $this->makeDirectoryFor($path);

        file_put_contents($path, $artifact->serialize());

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

    protected function makeDirectoryFor(string $path): void
    {
        $dir = pathinfo($path)["dirname"];
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
