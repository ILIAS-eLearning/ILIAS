<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCloudPluginService
 * Basic frame for the plugin service class probably needs to be overwritten
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 */
class ilCloudPluginService
{
    protected ?ilCloudPlugin $plugin_object = null;

    public function __construct(string $service_name, int $obj_id)
    {
        $this->plugin_object = ilCloudConnector::getPluginClass($service_name, $obj_id);
    }

    public function getPluginObject() : ilCloudPlugin
    {
        return $this->plugin_object;
    }

    /**
     * For shorter access
     */
    public function getPluginHookObject() : ilCloudHookPlugin
    {
        return $this->getPluginObject()->getPluginHookObject();
    }

    /**
     * For shorter access
     * @return ilCloudPluginConfig
     */
    public function getAdminConfigObject() : ilCloudPluginConfig
    {
        return $this->getPluginObject()->getAdminConfigObject();
    }

    /**
     * Called after the cloud object is created to authenticate the service if needed. The callback can be used to get
     * back to the correct place in ILIAS (the afterAuth Method) after the remote authentication.
     * @param string $callback_url
     */
    public function authService(string $callback_url = "")
    {
        header("Location: " . htmlspecialchars_decode($callback_url));
    }

    /**
     * Place were the callback should lead to after authentication. Can be used to updated plugin settings.
     * @return bool
     */
    public function afterAuthService() : bool
    {
        return true;
    }

    public function getServiceObject() : object
    {
    }

    /**
     * Called when RootId (id of the folder which is set to root) is needed.
     * Mostly after the base directory is changed by the user or after creating the cloud Obect
     */
    public function getRootId(string $root_path) : string
    {
        return "root";
    }

    /**
     * Updates the file tree when the user navigates through files and folders
     */
    public function addToFileTree(ilCloudFileTree $file_tree, string $parent_folder = "/") : void
    {
    }

    /**
     * Updates the file tree when the user navigates through files and folders.
     * Uses the id instead of the path.
     */
    public function addToFileTreeWithId(ilCloudFileTree $file_tree, string $id) : bool
    {
        return false;
    }

    /**
     * Called when a file is accessed for download by the user
     */
    public function getFile(?string $path = null, ilCloudFileTree $file_tree = null) : object
    {
    }

    /**
     * Called when a file is accessed for download by the user
     * Uses the id instead of the path.
     */
    public function getFileById(string $id) : bool
    {
        return false;
    }

    /**
     * Called when a folder is created by the user
     */
    public function createFolder(?string $path = null, ilCloudFileTree $file_tree = null) : void
    {
    }

    /**
     * Called when a folder is created by the user
     * Uses the id instead of the path.
     */
    public function createFolderById(string $parent_id, string $folder_name) : bool
    {
        return false;
    }

    /**
     * Called when a file is uploaded by the user
     */
    public function putFile(object $file, string $name, string $path = '', ilCloudFileTree $file_tree = null) : void
    {
    }

    /**
     * Called when a file is uploaded by the user
     * Uses the id instead of the path.
     */
    public function putFileById(string $tmp_name, string $file_name, string $id) : bool
    {
        return false;
    }

    /**
     * Called when an item is deleted by the user
     */
    public function deleteItem(?string $path = null, ilCloudFileTree $file_tree = null) : void
    {
    }

    /**
     * Called when an item is deleted by the user
     * Uses the id instead of the path.
     */
    public function deleteItemById(string $id) : bool
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

    public function formatBytes(int $bytes) : string
    {
        $unit = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($unit) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $unit[$pow];
    }

    /**
     * A little helper function returning the currently used protocol as string
     */
    public function getProtocol() : string
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'HTTPS' : 'HTTP';
    }
}
