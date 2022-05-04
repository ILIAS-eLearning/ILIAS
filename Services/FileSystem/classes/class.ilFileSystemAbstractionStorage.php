<?php

use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\Filesystem\Filesystem;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * @deprecated
 */
abstract class ilFileSystemAbstractionStorage
{
    public const STORAGE_WEB = 1;
    public const STORAGE_DATA = 2;
    public const STORAGE_SECURED = 3;
    private const FACTOR = 100;
    private const MAX_EXPONENT = 3;
    private const SECURED_DIRECTORY = "sec";
    private int $container_id;
    private int $storage_type;
    private bool $path_conversion = false;
    protected ?string $path = null;
    protected \ILIAS\Filesystem\Filesystems $file_system_service;
    
    /**
     * Constructor
     *
     * @param $a_storage_type    int storage type
     * @param $a_path_conversion bool En/Disable automatic path conversion.
     *                           If enabled files with id 123 will be stored in
     *                           directory files/1/file_123 object id of container
     * @param $a_container_id    int (e.g file_id or mob_id)
     *
     */
    public function __construct(int $a_storage_type, bool $a_path_conversion, int $a_container_id)
    {
        global $DIC;
        $this->storage_type = $a_storage_type;
        $this->path_conversion = $a_path_conversion;
        $this->container_id = $a_container_id;
        $this->file_system_service = $DIC->filesystem();
        
        // Get path info
        $this->init();
    }
    
    public function fileExists(string $a_absolute_path) : bool
    {
        return $this->getFileSystemService()->has($this->createRelativePathForFileSystem($a_absolute_path));
    }
    
    protected function getLegacyFullAbsolutePath(string $relative_path) : string
    {
        $stream = $this->getFileSystemService()->readStream($relative_path);
        
        return $stream->getMetadata('uri');
    }
    
    protected function getFileSystemService() : Filesystem
    {
        switch ($this->getStorageType()) {
            case self::STORAGE_DATA:
                return $this->file_system_service->storage();
            case self::STORAGE_WEB:
            case self::STORAGE_SECURED:
            case self::SECURED_DIRECTORY:
                return $this->file_system_service->web();
        }
        throw new LogicException('cannot determine correct filesystem');
    }
    
    public function getContainerId() : int
    {
        return $this->container_id;
    }
    
    public static function createPathFromId(int $a_container_id, string $a_name) : string
    {
        $path = [];
        $found = false;
        $num = $a_container_id;
        $path_string = '';
        for ($i = self::MAX_EXPONENT; $i > 0; $i--) {
            $factor = pow(self::FACTOR, $i);
            if (($tmp = (int) ($num / $factor)) or $found) {
                $path[] = $tmp;
                $num = $num % $factor;
                $found = true;
            }
        }
        
        if (count($path)) {
            $path_string = (implode('/', $path) . '/');
        }
        
        return $path_string . $a_name . '_' . $a_container_id;
    }
    
    /**
     * @deprecated Please use StorageService or FilesystemService for all file opertion
     */
    public function writeToFile(string $a_data, $a_absolute_path)
    {
        if (!$fp = @fopen($a_absolute_path, 'w+')) {
            return false;
        }
        if (@fwrite($fp, $a_data) === false) {
            @fclose($fp);
            return false;
        }
        @fclose($fp);
        return true;
    }
    
    /**
     * @deprecated Please use StorageService or FilesystemService for all file opertion
     */
    public function copyFile(string $a_from, string $a_to) : bool
    {
        if (@file_exists($a_from)) {
            @copy($a_from, $a_to);
            return true;
        }
        return false;
    }
    
    /**
     * Get path prefix. Prefix that will be prepended to the path
     * No trailing slash. E.g ilFiles for files
     */
    abstract protected function getPathPrefix() : string;
    
    /**
     * Get directory name. E.g for files => file
     * Only relative path, no trailing slash
     * '_<obj_id>' will be appended automatically
     */
    abstract protected function getPathPostfix() : string;
    
    public function create() : void
    {
        if (!$this->getFileSystemService()->has($this->path)) {
            $this->getFileSystemService()->createDir($this->path);
        }
    }
    
    /**
     * Calculates the full path on the filesystem.
     * This method is filesystem aware and will create the absolute path
     * if it's not already existing.
     *
     * @return string Absolute filesystem path.
     *
     * @throws IOException Thrown if the absolute path could not be created.
     */
    public function getAbsolutePath() : string
    {
        return $this->getLegacyAbsolutePath();
    }
    
    /**
     * Calculates the absolute filesystem storage location.
     *
     * @return string The absolute path.
     *
     * @throws IOException Thrown if the directory could not be created.
     */
    protected function getLegacyAbsolutePath() : string
    {
        if (!$this->getFileSystemService()->has($this->path)) {
            $this->getFileSystemService()->createDir($this->path);
        }
        
        if ($this->getStorageType() === self::STORAGE_DATA) {
            return CLIENT_DATA_DIR . '/' . $this->path;
        }
        return CLIENT_WEB_DIR . '/' . $this->path;
    }
    
    protected function init() : bool
    {
        switch ($this->storage_type) {
            case self::STORAGE_DATA:
            case self::STORAGE_WEB:
                break;
            case self::STORAGE_SECURED:
                $this->path = rtrim($this->path, '/') . '/' . self::SECURED_DIRECTORY . '/';
                break;
        }
        
        // Append path prefix
        $this->path .= ($this->getPathPrefix() . '/');
        
        if ($this->path_conversion) {
            $this->path .= self::createPathFromId($this->container_id, $this->getPathPostfix());
        } else {
            $this->path .= ($this->getPathPostfix() . '_' . $this->container_id);
        }
        
        return true;
    }
    
    public function delete() : bool
    {
        try {
            $this->getFileSystemService()->deleteDir($this->getAbsolutePath());
        } catch (Exception $e) {
            return false;
        }
        
        return true;
    }
    
    public function deleteDirectory(string $a_abs_name) : bool
    {
        $path = $this->createRelativePathForFileSystem($a_abs_name);
        $this->getFileSystemService()->deleteDir($path);
        return !$this->getFileSystemService()->has($path);
    }
    
    public function deleteFile(string $a_abs_name) : bool
    {
        $path = $this->createRelativePathForFileSystem($a_abs_name);
        $this->getFileSystemService()->delete($path);
        return !$this->getFileSystemService()->has($path);
    }
    
    public static function _copyDirectory(string $a_sdir, string $a_tdir) : bool
    {
        try {
            $sourceFS = LegacyPathHelper::deriveFilesystemFrom($a_sdir);
            $targetFS = LegacyPathHelper::deriveFilesystemFrom($a_tdir);
            
            $sourceDir = LegacyPathHelper::createRelativePath($a_sdir);
            $targetDir = LegacyPathHelper::createRelativePath($a_tdir);
            
            // check if arguments are directories
            if (!$sourceFS->hasDir($sourceDir)) {
                return false;
            }
            
            $sourceList = $sourceFS->listContents($sourceDir, true);
            
            foreach ($sourceList as $item) {
                if ($item->isDir()) {
                    continue;
                }
                
                $itemPath = $targetDir . '/' . substr($item->getPath(), strlen($sourceDir));
                $stream = $sourceFS->readStream($sourceDir);
                $targetFS->writeStream($itemPath, $stream);
            }
            
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
    
    public function appendToPath(string $a_appendix) : void
    {
        $this->path .= $a_appendix;
    }
    
    public function getStorageType() : int
    {
        return $this->storage_type;
    }
    
    public function getPath() : string
    {
        return $this->path;
    }
    
    private function createRelativePathForFileSystem(string $a_absolute_path) : string
    {
        $relative_path = ILIAS\Filesystem\Util\LegacyPathHelper::createRelativePath($a_absolute_path);
        
        return $relative_path;
    }
}
