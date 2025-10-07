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

namespace ILIAS\FileDelivery\Setup;

use ILIAS\Setup\Objective;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\Environment;

/**
 * @internal Only for usage in FileDelivery component
 */
abstract class BuildStaticConfigStoredObjective implements Objective
{
    protected static function saveName(string $name): string
    {
        return strtolower((string) preg_replace('/[^a-zA-Z0-9_]/', '_', $name));
    }

    final public static function PATH(): string
    {
        return realpath(__DIR__ . '/../../../../../public/data/')
            . '/' . self::saveName((new static())->getArtifactName()) . ".php";
    }

    abstract public function getArtifactName(): string;

    abstract public function build(): Artifact;

    public function buildIn(Environment $env): Artifact
    {
        return $this->build();
    }

    public function getPreconditions(Environment $environment): array
    {
        return [];
    }

    public function getHash(): string
    {
        return hash("sha256", $this->getArtifactName());
    }

    public function getLabel(): string
    {
        return 'Build ' . $this->getArtifactName() . ' Ststic Config';
    }

    public function isNotable(): bool
    {
        return true;
    }

    final protected function getPath(): string
    {
        return static::PATH();
    }

    public function achieve(Environment $environment): Environment
    {
        $artifact = $this->buildIn($environment);

        $path = $this->getPath();

        $this->makeDirectoryFor($path);

        file_put_contents($path, $artifact->serialize());

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return true;
    }

    protected function makeDirectoryFor(string $path): void
    {
        $dir = pathinfo($path)["dirname"];
        if (!file_exists($dir) && (!mkdir($dir, 0755, true) && !is_dir($dir))) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }
}
