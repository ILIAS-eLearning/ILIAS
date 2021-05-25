<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

/**
 * Import directory interface
 *
 * @author	Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup	ServicesExport
 */
class ilExportImportDirectory extends ilImportDirectory
{
    private const PATH_PREFIX = 'export';

    /**
     * @return string
     */
    protected function getPathPrefix() : string
    {
        return self::PATH_PREFIX;
    }

    /**
     * @param int    $user_id
     * @param string $type
     */
    public function hasFilesFor(int $user_id, string $type) : bool
    {
        return (bool) count($this->getFilesFor($user_id, $type));
    }

    /**
     * @param int    $user_id
     * @param string $type
     * @return array
     */
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
        if ($this->storage->hasDir($this->getRelativePath() . '/' . (string) $user_id)) {
            $finder = $this->storage->finder()->in([$this->getRelativePath() . '/' . (string) $user_id])
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
     * @param string $type
     * @return bool
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

    /**
     * @param int    $user_id
     * @param string $type
     * @param string $post_hash
     * @return string
     */
    public function getAbsolutePathForHash(int $user_id, string $type, string $post_hash) : string
    {
        foreach ($this->getFilesFor($user_id, $type) as $hash => $file) {
            if (strcmp($hash, $post_hash) === 0) {
                $file_path = base64_decode($hash);
                return ilUtil::getDataDir() . '/' . base64_decode($hash);
            }
        }
        return '';
    }
}
