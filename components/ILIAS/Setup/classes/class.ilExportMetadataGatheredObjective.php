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

use ILIAS\Setup;

class ilExportMetadataGatheredObjective implements Setup\Objective
{
    public const EXPORT_META = "export_meta";

    /**
     * @inheritdoc
     */
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return "Gathering export metadata";
    }

    /**
     * @inheritdoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $meta = [
          "user_name=" . posix_getpwuid(posix_geteuid())['name'],
          "created=" . date('Y-m-d H:i:s'),
          "commit=" . $this->getCurrentGitCommitHash(),
          "host=" . gethostname()
        ];

        return $environment->withConfigFor(self::EXPORT_META, $meta);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

    protected function getCurrentGitCommitHash(): string
    {
        $path = realpath('.git') . DIRECTORY_SEPARATOR;

        if (!$path) {
            return "";
        }

        $head = trim(substr(file_get_contents($path . 'HEAD'), 4));
        $hash = trim(file_get_contents($path . $head));

        return $hash;
    }
}
