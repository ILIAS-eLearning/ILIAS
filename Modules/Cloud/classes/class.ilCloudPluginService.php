<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCloudPluginService
 *
 * Basic frame for the plugin service class probably needs to be overwritten
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 * @ingroup ModulesCloud
 */
class ilCloudPluginService
{
    /**
     * @var ilCloudPlugin $object
     */
    protected $plugin_object = null;

    /**
     * @param $service_name
     * @param $obj_id
     */
    public function __construct($service_name, $obj_id)
    {
        $this->plugin_object = ilCloudConnector::getPluginClass($service_name, $obj_id);
    }

    /**
     * @return ilCloudPlugin
     */
    public function getPluginObject()
    {
        return $this->plugin_object;
    }

    /**
     * For shorter access
     * @return ilCloudHookPlugin
     */
    public function getPluginHookObject()
    {
        return $this->getPluginObject()->getPluginHookObject();
    }

    /**
     * For shorter access
     * @return ilCloudPluginConfig
     */
    public function getAdminConfigObject()
    {
        return $this->getPluginObject()->getAdminConfigObject();
    }

    /**
     * Called after the cloud object is created to authenticate the service if needed. The callback can be used to get
     * back to the correct place in ILIAS (the afterAuth Method) after the remote authentication.
     *
     * @param string $callback_url
     */
    public function authService($callback_url = "")
    {
        header("Location: " . htmlspecialchars_decode($callback_url));
    }

    /**
     * Place were the callback should lead to after authentication. Can be used to updated plugin settings.
     * @return bool
     */
    public function afterAuthService()
    {
        return true;
    }

    public function getServiceObject()
    {
    }

    /**
     * Called when RootId (id of the folder which is set to root) is needed.
     * Mostly after the base directory is changed by the user or after creating the cloud Obect
     *
     * @param $root_path
     * @return string
     */
    public function getRootId($root_path)
    {
        return "root";
    }

    /**
     * Updates the file tree when the user navigates through files and folders
     * @param ilCloudFileTree $file_tree
     * @param string $parent_folder
     */
    public function addToFileTree(ilCloudFileTree $file_tree, $parent_folder = "/")
    {
    }


    /**
     * Updates the file tree when the user navigates through files and folders.
     * Uses the id instead of the path.
     *
     * @param ilCloudFileTree $file_tree
     * @param string          $id
     *
     * @return bool
     */
    public function addToFileTreeWithId(ilCloudFileTree $file_tree, $id)
    {
        return false;
    }

    /**
     * Called when a file is accessed for download by the user
     * @param null $path
     * @param ilCloudFileTree $file_tree
     */
    public function getFile($path = null, ilCloudFileTree $file_tree = null)
    {
    }


    /**
     * Called when a file is accessed for download by the user
     * Uses the id instead of the path.
     *
     * @param string $id
     *
     * @return bool
     */
    public function getFileById($id)
    {
        return false;
    }

    /**
     * Called when a folder is created by the user
     * @param null $path
     * @param ilCloudFileTree $file_tree
     */
    public function createFolder($path = null, ilCloudFileTree $file_tree = null)
    {
    }


    /**
     * Called when a folder is created by the user
     * Uses the id instead of the path.
     * @param string $parent_id
     * @param string $folder_name
     *
     * @return string|bool
     */
    public function createFolderById($parent_id, $folder_name)
    {
        return false;
    }


    /**
     * Called when a file is uploaded by the user
     *
     * @param                 $file
     * @param                 $name
     * @param string          $path
     * @param ilCloudFileTree $file_tree
     */
    public function putFile($file, $name, $path = '', ilCloudFileTree $file_tree = null)
    {
    }


    /**
     * Called when a file is uploaded by the user
     * Uses the id instead of the path.
     *
     * @param string $tmp_name
     * @param string $file_name
     * @param string $id
     *
     * @return bool
     */
    public function putFileById($tmp_name, $file_name, $id)
    {
        return false;
    }

    /**
     * Called when an item is deleted by the user
     * @param null $path
     * @param ilCloudFileTree $file_tree
     */
    public function deleteItem($path = null, ilCloudFileTree $file_tree = null)
    {
    }


    /**
     * Called when an item is deleted by the user
     * Uses the id instead of the path.
     *
     * @param string $id
     *
     * @return bool
     */
    public function deleteItemById($id)
    {
        return false;
    }

    /**
     * by default false
     * @return bool
     */
    public function isCaseSensitive()
    {
        return false;
    }

    /**
     * @param int $bytes
     * @return string
     */
    public function formatBytes($bytes)
    {
        $unit  = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($unit) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $unit[$pow];
    }

    /**
     * A little helper function returning the currently used protocol as string
     * @return string
     */
    public function getProtokol()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'HTTPS' : 'HTTP';
    }
}
