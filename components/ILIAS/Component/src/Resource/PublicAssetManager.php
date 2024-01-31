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

namespace ILIAS\Component\Resource;

/**
 * Will take care of the public assets, just like a good manager does.
 */
class PublicAssetManager
{
    public const REGEXP = '%^(/\w+)+$%';

    protected $assets = [];


    public function addAssets(PublicAsset ...$assets): void
    {
        foreach ($assets as $asset) {
            $this->insertInto($this->assets, explode("/", $asset->getTarget()), $asset);
        }
    }

    protected function insertInto(array &$assets, array $path, PublicAsset $asset): void
    {
        $key = array_shift($path);
        $key_exists = array_key_exists($key, $assets);
        $target_reached = count($path) === 0;

        if (!$key_exists && $target_reached) {
            $assets[$key] = $asset;
            return;
        }

        if (!$target_reached && (!$key_exists || is_array($assets[$key]))) {
            if (!$key_exists) {
                $assets[$key] = [];
            }
            $this->insertInto($assets[$key], $path, $asset);
            return;
        }

        $first_asset = $assets[$key];
        while(!$first_asset instanceof PublicAsset) {
            $first_asset = array_shift($first_asset);
        }

        throw new \LogicException(
            "There are (at least) two assets for the same target '{$asset->getTarget()}': " .
            "'{$first_asset->getSource()}' and '{$asset->getSource()}'"
        );
    }

    /**
     * @param string $ilias_base full path to ILIAS base folder
     * @param string $target full path to public folder
     */
    public function buildPublicFolder(string $ilias_base, string $target): void
    {
        if (!preg_match(self::REGEXP, $ilias_base)) {
            throw new \InvalidArgumentException(
                "'{$ilias_base}' is not a valid path to ILIAS base folder."
            );
        }
        if (!preg_match(self::REGEXP, $target)) {
            throw new \InvalidArgumentException(
                "'{$target}' is not a valid target path for public assets."
            );
        }

        $this->purge($target);
        $this->makeDir($target);
        $this->buildPublicFolderRecursivelyArray($ilias_base, $target, $this->assets);
    }

    protected function buildPublicFolderRecursively(string $ilias_base, string $target, PublicAsset|array $asset): void
    {
        if (is_array($asset)) {
            $this->makeDir("$target");
            $this->buildPublicFolderRecursivelyArray($ilias_base, $target, $asset);
        } else {
            $targets = explode("/", $asset->getTarget());
            $this->copy("$ilias_base/{$asset->getSource()}", "$target");
        }
    }

    protected function buildPublicFolderRecursivelyArray(string $ilias_base, string $target, array $assets): void
    {
        foreach ($assets as $key => $asset) {
            $this->buildPublicFolderRecursively($ilias_base, "$target/$key", $asset);
        }
    }

    protected function copy(string $source, string $target): void
    {
        if (is_file($source)) {
            copy($source, $target);
        } elseif (is_dir($source)) {
            $dir = new \RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
            foreach($dir as $d) {
                $name = $d->getBasename();
                $this->makeDir("$target/$name");
                $this->copy("$source/$name", "$target/$name");
            }
        } else {
            throw new \RuntimeException(
                "Cannot copy $source, not a file or directory."
            );
        }
    }

    protected function purge(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $d = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $r = new \RecursiveIteratorIterator($d, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($r as $file) {
            $file->isDir() ? rmdir((string) $file) : unlink((string) $file);
        }
        rmdir($path);
    }

    protected function makeDir(string $path): void
    {
        mkdir($path, 0755);
    }
}
