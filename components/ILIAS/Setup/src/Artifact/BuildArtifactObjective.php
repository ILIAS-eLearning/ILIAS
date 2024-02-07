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
    private const COMPONENTS_DIRECTORY = "components";

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
        return 'Build ' . $this->getRelativeArtifactPath();
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

        $path = $this->getRelativeArtifactPath();

        $this->makeDirectoryFor($path);

        file_put_contents($path, $artifact->serialize());

        return $environment;
    }

    private function getRelativeArtifactPath(): string
    {
        $here = realpath(__DIR__ . "/../../../../../");

        $artifact_path = $this->getArtifactPath();

        switch (true) {
            case strpos($artifact_path, "/") === 0:
            case strpos($artifact_path, "./") === 0:
                $path = $this->realpath($artifact_path);
                break;
            case strpos($artifact_path, "../" . self::COMPONENTS_DIRECTORY . "") === 0:
                $path = $this->realpath($here . "/" . self::COMPONENTS_DIRECTORY . "/" . $artifact_path);
                break;

            case strpos($artifact_path, "../") === 0:
                $dirname = dirname((new \ReflectionClass($this))->getFileName());
                $path = $this->realpath($dirname . "/" . $artifact_path);
                break;
            default:
                $path = $this->realpath($artifact_path);
                break;
        }

        return "./" . ltrim(str_replace($here, "", $path), "/");
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

    /**
     * @description we cannot use php's realpath because it does not work with with non existing paths.
     *              Thanks to Beat Christen, https://stackoverflow.com/questions/20522605/what-is-the-best-way-to-resolve-a-relative-path-like-realpath-for-non-existing
     */
    protected function realpath(string $filename): string
    {
        $path = [];
        foreach (explode('/', $filename) as $part) {
            // ignore parts that have no value
            if (empty($part) || $part === '.') {
                continue;
            }

            if ($part !== '..') {
                // cool, we found a new part
                $path[] = $part;
            } elseif (count($path) > 0) {
                // going back up? sure
                array_pop($path);
            } else {
                // now, here we don't like
                throw new \RuntimeException('Climbing above the root is not permitted.');
            }
        }

        return "/" . implode('/', $path);
    }
}
