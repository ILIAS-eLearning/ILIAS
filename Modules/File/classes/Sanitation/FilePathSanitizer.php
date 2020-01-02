<?php

namespace ILIAS\File\Sanitation;

use DirectoryIterator;
use Exception;
use ilFileUtils;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ilObjFile;

/**
 * Class FilePathSanitizer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class FilePathSanitizer
{

    /**
     * @var ilObjFile
     */
    private $file_object;
    /**
     * @var string
     */
    private $relative_path;
    /**
     * @var \ILIAS\Filesystem\Filesystem
     */
    private $fs;
    /**
     * @var string
     */
    private $absolute_path;


    /**
     * FilePathSanitizer constructor.
     *
     * @param ilObjFile $file_object
     */
    public function __construct(ilObjFile $file_object)
    {
        $this->file_object = $file_object;
        $this->absolute_path = $this->file_object->getDirectory($this->file_object->getVersion()) . "/" . $this->file_object->getFileName();
        $this->relative_path = LegacyPathHelper::createRelativePath($this->absolute_path);
        $this->fs = LegacyPathHelper::deriveFilesystemFrom($this->absolute_path);
    }


    /**
     * @return bool
     */
    public function needsSanitation() /* : bool*/
    {
        try {
            $fs_relative_path_existing = $this->fs->has($this->relative_path);
            $fs_valid_relative_path_existing = $this->fs->has(ilFileUtils::getValidFilename($this->relative_path));
            $native_absolute_path_exists = file_exists($this->absolute_path);
            $native_valid_absolute_path_existing = file_exists(ilFileUtils::getValidFilename($this->absolute_path));

            return (
                !$fs_relative_path_existing
                || !$fs_valid_relative_path_existing
                || !$native_absolute_path_exists
                || !$native_valid_absolute_path_existing
            );
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * @param string $message
     */
    private function log(/*string*/ $message)
    {
        global $DIC;
        $DIC->logger()->root()->debug("FilePathSanitizer: " . $message);
    }


    /**
     * @return bool
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     */
    public function sanitizeIfNeeded() /* : void */
    {
        if ($this->needsSanitation()) {
            // First Try: using FileSystemService
            $dirname = dirname($this->relative_path);
            if (!$this->fs->has($dirname)) {
                $this->log("FAILED: Sanitizing File Path: {$this->file_object->getFile()}. Message: Directory not found");

                return false;
            }
            $first_file = reset($this->fs->listContents($dirname));
            if ($first_file instanceof \ILIAS\Filesystem\DTO\Metadata) {
                try {
                    $valid_filename = $this->santitizeFilename($first_file->getPath());
                    // rename file in filesystem
                    if (!$this->fs->has($valid_filename)) {
                        $this->fs->rename($first_file->getPath(), $valid_filename);
                        // rename file object

                        $this->log("Sanitized File Path: {$valid_filename}");
                    }
                    $this->saveNewNameForFileObject($valid_filename);

                    return true;
                } catch (Exception $e) {
                    $this->log("FAILED: Sanitizing File Path: {$this->file_object->getFile()}. Message: {$e->getMessage()}. Will try using native PHP");

                    try {
                        // Second try: use native php
                        $scandir = scandir(dirname($this->absolute_path));
                        if (isset($scandir[2])) {
                            $first_file = $scandir[2];
                            if (is_file($first_file)) {
                                $valid_filename = $this->santitizeFilename($first_file);
                                if (rename($first_file, $valid_filename)) {
                                    $this->saveNewNameForFileObject($valid_filename);
                                    $this->log("Sanitized File Path: {$valid_filename}");
                                }
                            } else {
                                throw new Exception("is not a file: " . $first_file);
                            }
                        } else {
                            throw new Exception("no File found in " . dirname($this->absolute_path));
                        }
                    } catch (Exception $e) {
                        $this->log("FAILED AGAIN: Sanitizing File Path: {$this->file_object->getFile()}. Message: {$e->getMessage()}");

                        try {
                            foreach (new DirectoryIterator(dirname($this->absolute_path)) as $item) {
                                if ($item->isDot()) {
                                    continue;
                                }
                                if ($item->isFile()) {
                                    $valid_filename = $this->santitizeFilename($item->getPathname());
                                    if (rename($item->getPathname(), $valid_filename)) {
                                        $this->saveNewNameForFileObject($valid_filename);
                                        $this->log("Sanitized File Path: {$valid_filename}");
                                    }
                                    break;
                                }
                            }
                        } catch (Exception $e) {
                            $this->log("FAILED AGAIN and AGAIN: Sanitizing File Path: {$this->file_object->getFile()}. Message: {$e->getMessage()}");
                        }
                    }

                    return false;
                }
            }

            return false;
        }

        return true;
    }


    /**
     * @param $first_file
     *
     * @return string|string[]|null
     * @throws \ilFileUtilsException
     */
    private function santitizeFilename($first_file)
    {
        $valid_filename = $first_file;

        while (preg_match('#\p{C}+|^\./#u', $valid_filename)) {
            $valid_filename = preg_replace('#\p{C}+|^\./#u', '', $valid_filename);
        }

        $valid_filename = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $valid_filename); // removes all non printable characters (ASCII 7 Bit)

        // $valid_filename = \League\Flysystem\Util::normalizeRelativePath($valid_filename);
        // $valid_filename = preg_replace('/[\x00-\x1F\x7F-\xA0\xAD]/u', '', $valid_filename);
        // $valid_filename = iconv(mb_detect_encoding($valid_filename, mb_detect_order(), true), "UTF-8", $valid_filename);
        // $valid_filename = utf8_encode($valid_filename);

        $valid_filename = ilFileUtils::getValidFilename($valid_filename);

        return $valid_filename;
    }


    /**
     * @param $valid_filename
     */
    private function saveNewNameForFileObject($valid_filename)
    {
        $sanitized_filename = basename($valid_filename);
        $this->file_object->setFileName($sanitized_filename);
        $this->file_object->update();
    }
}
