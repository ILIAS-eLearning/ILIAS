<?php

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

declare(strict_types=1);

namespace ILIAS\Setup\Artifact;

use ILIAS\Setup;

/**
 * This is an objective to build some artifact.
 *
 * @deprecated not deprecated in the true sense of the word, but we will be making major changes to the artifacts
 * infrastructure, see for example https://github.com/ILIAS-eLearning/ILIAS/pull/7013#pullrequestreview-1869594435
 *
 * therefore: if you are currently implementing it, please contact Richard Klees or Fabian Schmid
 */
abstract class BuildArtifactObjective implements Setup\Objective
{
    private const ARTIFACTS = __DIR__ . "/../../../../../artifacts";

    /**
     * @return string The path where the artifact should be stored. You can use this path to require the artifact.
     */
    final public static function PATH(): string
    {
        return realpath(self::ARTIFACTS) . "/" . md5(static::class) . ".php";
    }

    private const COMPONENTS_DIRECTORY = "components";

    /**
     * Get the filename where the builder wants to put its artifact.
     *
     * This is understood to be a path relative to the ILIAS root directory.
     */
    abstract public function getArtifactName(): string;

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
        return hash("sha256", $this->getArtifactName());
    }

    /**
     * Defaults to 'Build ' . $this->getArtifactName().' Artifact'.
     *
     * @inheritdocs
     */
    public function getLabel(): string
    {
        return 'Build ' . $this->getArtifactName() . ' Artifact';
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

        $path = static::PATH();

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
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }
    }
}
