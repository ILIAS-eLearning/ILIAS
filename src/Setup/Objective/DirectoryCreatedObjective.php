<?php declare(strict_types=1);

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
 
namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * Create a directory.
 */
class DirectoryCreatedObjective implements Setup\Objective
{
    const DEFAULT_DIRECTORY_PERMISSIONS = 0755;

    protected string $path;
    protected int $permissions;

    public function __construct(
        string $path,
        int $permissions = self::DEFAULT_DIRECTORY_PERMISSIONS
    ) {
        if ($path == "") {
            throw new \InvalidArgumentException(
                "Path is empty."
            );
        }
        $this->path = $path;
        $this->permissions = $permissions;
    }

    /**
     * Uses hashed Path.
     *
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash("sha256", self::class . "::" . $this->path);
    }

    /**
     * Defaults to "Build $this->getArtifactPath()".
     *
     * @inheritdocs
     */
    public function getLabel() : string
    {
        return "Create directory '$this->path'";
    }

    /**
     * Defaults to 'true'.
     *
     * @inheritdocs
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        if (file_exists($this->path)) {
            return [];
        }
        return [
            new Setup\Condition\CanCreateDirectoriesInDirectoryCondition(dirname($this->path))
        ];
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        mkdir($this->path, $this->permissions);

        if (!is_dir($this->path)) {
            throw new Setup\UnachievableException(
                "Could not create directory '$this->path'"
            );
        }
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return !file_exists($this->path);
    }
}
