<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * @defgroup ServicesFileSystemStorage Services/FileSystem
 *
 * @author   Stefan Meyer <meyer@leifos.com>
 * @version  $Id$
 *

 */
abstract class ilFileSystemAbstractionStorage
{
    const STORAGE_WEB = 1;
    const STORAGE_DATA = 2;
    const STORAGE_SECURED = 3;
    const FACTOR = 100;
    const MAX_EXPONENT = 3;
    const SECURED_DIRECTORY = "sec";
    private $container_id;
    private $storage_type;
    private $path_conversion = false;
    protected $path;


    /**
     * Constructor
     *
     * @access public
     *
     * @param int                                                         storage type
     * @param bool                                                        En/Disable automatic path
     *                                                                    conversion. If enabled
     *                                                                    files with id 123 will be
     *                                                                    stored in directory
     *                                                                    files/1/file_123
     * @param int                                                         object id of container
     *                                                                           (e.g file_id or
     *                                                                           mob_id)
     *
     */
    public function __construct($a_storage_type, $a_path_conversion, $a_container_id)
    {
        $this->storage_type = $a_storage_type;
        $this->path_conversion = $a_path_conversion;
        $this->container_id = $a_container_id;

        // Get path info
        $this->init();
    }


    /**
     * @param $a_absolute_path
     *
     * @return bool
     */
    public function fileExists($a_absolute_path)
    {
        $relative_path = $this->createRelativePathForFileSystem($a_absolute_path);

        return $this->getFileSystemService()->has($relative_path);
    }


    /**
     * @param $relative_path
     *
     * @return array|mixed|null
     */
    protected function getLegacyFullAbsolutePath($relative_path)
    {
        $stream = $this->getFileSystemService()->readStream($relative_path);

        return $stream->getMetadata('uri');
    }


    /**
     * @return \ILIAS\Filesystem\Filesystem
     */
    protected function getFileSystemService()
    {
        global $DIC;
        switch ($this->getStorageType()) {
            case self::STORAGE_DATA:
                return $DIC->filesystem()->storage();
                break;
            case self::STORAGE_WEB:
            case self::SECURED_DIRECTORY:
                return $DIC->filesystem()->web();
                break;
        }
    }


    public function getContainerId()
    {
        return $this->container_id;
    }


    /**
     * Create a path from an id: e.g 12345 will be converted to 12/34/<name>_5
     *
     * @param $a_container_id
     * @param $a_name
     *
     * @return string
     */
    public static function _createPathFromId($a_container_id, $a_name)
    {
        $path = array();
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
     * Get path prefix. Prefix that will be prepended to the path
     * No trailing slash. E.g ilFiles for files
     *
     * @abstract
     * @access protected
     *
     * @return string path prefix e.g files
     */
    abstract protected function getPathPrefix();


    /**
     * Get directory name. E.g for files => file
     * Only relative path, no trailing slash
     * '_<obj_id>' will be appended automatically
     *
     * @abstract
     * @access protected
     *
     * @return string directory name
     */
    abstract protected function getPathPostfix();


    /**
     * Create directory
     *
     * @access public
     *
     */
    public function create()
    {
        if (!$this->getFileSystemService()->has($this->path)) {
            $this->getFileSystemService()->createDir($this->path);
        }

        return true;
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
    public function getAbsolutePath()
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
    protected function getLegacyAbsolutePath()
    {
        if (!$this->getFileSystemService()->has($this->path)) {
            $this->getFileSystemService()->createDir($this->path);
        }

        if ($this->getStorageType() === self::STORAGE_DATA) {
            return CLIENT_DATA_DIR . '/' . $this->path;
        }
        return CLIENT_WEB_DIR . '/' . $this->path;
    }


    /**
     * Read path info
     *
     * @access private
     */
    protected function init()
    {
        switch ($this->storage_type) {
            case self::STORAGE_DATA:
            case self::STORAGE_WEB:
                break;
            case self::STORAGE_SECURED:
                $this->path .= '/' . self::SECURED_DIRECTORY;
                break;
        }

        // Append path prefix
        $this->path .= ($this->getPathPrefix() . '/');

        if ($this->path_conversion) {
            $this->path .= self::_createPathFromId($this->container_id, $this->getPathPostfix());
        } else {
            $this->path .= ($this->getPathPostfix() . '_' . $this->container_id);
        }

        return true;
    }


    /**
     * @param $a_data
     * @param $a_absolute_path
     *
     * @return bool
     */
    public function writeToFile($a_data, $a_absolute_path)
    {
        $relative_path = $this->createRelativePathForFileSystem($a_absolute_path);
        try {
            $this->getFileSystemService()->write($relative_path, $a_data);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }


    /**
     * @param $a_absolute_path
     *
     * @return bool
     */
    public function deleteFile($a_absolute_path)
    {
        $relative_path = $this->createRelativePathForFileSystem($a_absolute_path);
        if ($this->getFileSystemService()->has($relative_path)) {
            try {
                $this->getFileSystemService()->delete($relative_path);
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param $a_absolute_path
     *
     * @return bool
     */
    public function deleteDirectory($a_absolute_path)
    {
        $relative_path = $this->createRelativePathForFileSystem($a_absolute_path);
        if ($this->getFileSystemService()->has($relative_path)) {
            try {
                $this->getFileSystemService()->deleteDir($relative_path);
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }


    /**
     * @return bool
     */
    public function delete()
    {
        try {
            $this->getFileSystemService()->deleteDir($this->getAbsolutePath());
        } catch (Exception $e) {
            return false;
        }

        return true;
    }


    /**
     * @param $a_from
     * @param $a_to
     *
     * @return bool
     */
    public function copyFile($a_from, $a_to)
    {
        $relative_path_from = $this->createRelativePathForFileSystem($a_from);
        $relative_path_to = $this->createRelativePathForFileSystem($a_to);
        if ($this->getFileSystemService()->has($relative_path_from)) {
            try {
                $this->getFileSystemService()->copy($relative_path_from, $relative_path_to);
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param $a_sdir
     * @param $a_tdir
     *
     * @return bool
     */
    public static function _copyDirectory($a_sdir, $a_tdir)
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


    /**
     * @param string $a_appendix
     */
    public function appendToPath($a_appendix)
    {
        $this->path .= $a_appendix;
    }


    /**
     * @return int
     */
    public function getStorageType()
    {
        return $this->storage_type;
    }


    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * @param $a_absolute_path
     *
     * @return string
     */
    private function createRelativePathForFileSystem($a_absolute_path)
    {
        $relative_path = ILIAS\Filesystem\Util\LegacyPathHelper::createRelativePath($a_absolute_path);

        return $relative_path;
    }
}
