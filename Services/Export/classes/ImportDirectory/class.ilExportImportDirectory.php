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

use ILIAS\Filesystem\Filesystem;

/**
 * Import directory
 * @author     Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup    ServicesExport
 */
class ilExportImportDirectory extends ilImportDirectory
{
    private const PATH_PREFIX = 'export';

    protected function getPathPrefix() : string
    {
        return self::PATH_PREFIX;
    }

    public function hasFilesFor(int $user_id, string $type) : bool
    {
        return (bool) count($this->getFilesFor($user_id, $type));
    }

    public function getFilesFor(int $user_id, string $type) : array
    {
        if (!$this->exists()) {
            return [];
        }
        $finder = $this->storage->finder()
                                ->in([$this->getRelativePath()])
                                ->files()
                                ->depth('< 1')
                                ->sortByName();
        $files = [];
        foreach ($finder as $file) {
            $basename = basename($file->getPath());
            if ($this->matchesType($type, $basename)) {
                $files[base64_encode($file->getPath())] = $basename;
            }
        }
        if ($this->storage->hasDir($this->getRelativePath() . '/' . $user_id)) {
            $finder = $this->storage->finder()->in([$this->getRelativePath() . '/' . $user_id])
                                    ->depth('< 1')
                                    ->files()
                                    ->sortByName();
            foreach ($finder as $file) {
                $basename = basename($file->getPath());
                if ($this->matchesType($type, $basename)) {
                    $files[base64_encode($file->getPath())] = $basename;
                }
            }
        }
        asort($files);
        return $files;
    }

    /**
     * Check if filename matches a given type
     */
    protected function matchesType(string $type, string $filename) : bool
    {
        $matches = [];
        $result = preg_match('/[0-9]{10}__[0-9]{1,6}__([a-z]{1,4})_[0-9]{2,9}.zip/', $filename, $matches);
        if (!$result) {
            return false;
        }
        if (isset($matches[1]) && $matches[1] == $type) {
            return true;
        }
        return false;
    }

    public function getAbsolutePathForHash(int $user_id, string $type, string $post_hash) : string
    {
        foreach ($this->getFilesFor($user_id, $type) as $hash => $file) {
            if (strcmp($hash, $post_hash) === 0) {
                $file_path = base64_decode($hash);
                return ilFileUtils::getDataDir() . '/' . base64_decode($hash);
            }
        }
        return '';
    }
}
