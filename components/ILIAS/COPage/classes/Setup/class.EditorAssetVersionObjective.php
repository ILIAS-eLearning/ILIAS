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

namespace ILIAS\COPage\Setup;

use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact\BuildArtifactObjective;

class EditorAssetVersionObjective extends BuildArtifactObjective
{
    public function getArtifactName(): string
    {
        return "copage_editor_asset_version";
    }

    public function build(): Artifact
    {
        $files = [];
        foreach (["Editor/js", "PC/InteractiveImage/js"] as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    dirname(__DIR__, 2) . "/" . $directory,
                    \FilesystemIterator::SKIP_DOTS
                )
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $files[] = $directory . "/" . $iterator->getSubPathName();
                }
            }
        }
        sort($files, SORT_STRING);

        $hash = hash_init("sha256");
        foreach ($files as $file) {
            hash_update($hash, $file);
            hash_update_file($hash, dirname(__DIR__, 2) . "/" . $file);
        }

        return new ArrayArtifact([substr(hash_final($hash), 0, 16)]);
    }

    public static function getVersion(): string
    {
        $path = self::PATH();
        if (!is_readable($path)) {
            throw new \RuntimeException(
                "Missing COPage editor asset version artifact. Run setup build."
            );
        }

        $artifact = require $path;
        $version = is_array($artifact) ? ($artifact[0] ?? null) : null;
        if (!is_string($version) || !preg_match('/^[a-f0-9]{16}$/', $version)) {
            throw new \RuntimeException("Invalid COPage editor asset version artifact.");
        }

        return $version;
    }
}
