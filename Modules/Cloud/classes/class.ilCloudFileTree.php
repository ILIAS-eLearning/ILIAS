<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Cloud/exceptions/class.ilCloudException.php");
require_once("class.ilCloudFileNode.php");
require_once("class.ilCloudConnector.php");
require_once("class.ilCloudUtil.php");

/**
 * ilCloudFileTree class
 * Representation of the structure of all files and folders so far. Not really a tree but a list simulating a tree
 * (for faster access on the nodes). This class also calls the functions of a service to update the tree (addToFileTree,
 * deleteItem, etc.)
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @ingroup ModulesCloud
 */
class ilCloudFileTree
{

    /** id of the ilCloudFileTree, equals the object_id of the calling object or gui_class */
    protected int $id = 0;
    protected ?ilCloudFileNode $root_node = null;
    /** Path to $root_node ($root_node has always path "/", root_path is the path which can be changed in the settings) */
    protected string $root_path = "";
    protected array $item_list = [];
    /** Only for better performance */
    protected array $id_to_path_map = [];
    protected string $service_name = "";
    protected bool $case_sensitive = false;

    public function __construct(string $root_path = "/", string $root_id = "root", int $id, string $service_name)
    {
        $this->setId($id);
        $this->root_node = $this->createNode($root_path, $root_id, true);
        $this->setServiceName($service_name);
        $service = ilCloudConnector::getServiceClass($service_name, $id);
        $this->setCaseSensitive($service->isCaseSensitive());
    }

    protected function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    protected function setRootPath(string $path = "/") : void
    {
        $this->root_path = ilCloudUtil::normalizePath($path);
    }

    public function getRootPath() : string
    {
        return $this->root_path;
    }

    protected function setServiceName(string $service_name) : void
    {
        $this->service_name = $service_name;
    }

    public function getServiceName() : string
    {
        return $this->service_name;
    }

    public function isCaseSensitive() : bool
    {
        return $this->case_sensitive;
    }

    public function setCaseSensitive(bool $case_sensitive)
    {
        $this->case_sensitive = $case_sensitive;
    }

    public function getRootNode() : ilCloudFileNode|null
    {
        return $this->root_node;
    }

    protected function createNode(string $path = "/", int $id, bool $is_dir = false) : ilCloudFileNode
    {
        $node = new ilCloudFileNode(ilCloudUtil::normalizePath($path), $id);
        $this->item_list[$node->getPath()] = $node;
        $this->id_to_path_map[$node->getId()] = $node->getPath();
        $node->setIsDir($is_dir);
        return $node;
    }

    public function addNode(
        string $path,
        int $id,
        string $is_Dir,
        bool $modified = null,
        int $size = 0
    ) : ilCloudFileNode {
        $path = ilCloudUtil::normalizePath($path);
        $node = $this->getNodeFromPath($path);

        //node does not yet exist
        if (!$node) {
            if ($this->getNodeFromId($id)) {
                throw new ilCloudException(ilCloudException::ID_ALREADY_EXISTS_IN_FILE_TREE_IN_SESSION);
            }
            $path_of_parent = ilCloudUtil::normalizePath(dirname($path));
            $node_parent = $this->getNodeFromPath($path_of_parent);
            if (!$node_parent) {
                throw new ilCloudException(ilCloudException::PATH_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION,
                    "Parent: " . $path_of_parent);
            }
            $node = $this->createNode($path, $id, $is_Dir);
            $node->setParentId($node_parent->getId());
            $node_parent->addChild($node->getPath());
        }

        $node->setSize($size);
        $node->setModified($modified);

        return $node;
    }

    /**
     * Add node that relies on id's
     * @return ilCloudFileNode
     * @throws ilCloudException
     */
    public function addIdBasedNode(
        string $path,
        int $id,
        int $parent_id,
        bool $is_Dir,
        ?bool $modified = null,
        int $size = 0
    ) : ilCloudFileNode {
        $path = ilCloudUtil::normalizePath($path);
        $node = $this->getNodeFromPath($path);

        //node does not yet exist
        if (!$node) {
            $nodeFromId = $this->getNodeFromId($id);
            // If path isn't found but id is there -> Path got changed
            if ($nodeFromId) {
                // Adjust path
                $nodeFromId->setPath($path);
            }

            $node_parent = $this->getNodeFromId($parent_id);
            if (!$node_parent) {
                throw new ilCloudException(ilCloudException::PATH_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION,
                    "Parent: " . $parent_id);
            }
            $node = $this->createNode($path, $id, $is_Dir);
            $node->setParentId($node_parent->getId());
            $node_parent->addChild($node->getPath());
        }

        $node->setSize($size);
        $node->setModified($modified);

        return $node;
    }

    public function removeNode(string $path) : void
    {
        $node = $this->getNodeFromPath($path);
        $parent = $this->getNodeFromId($node->getParentId());
        $parent->removeChild($path);
        unset($this->item_list[$node->getPath()]);
        unset($this->id_to_path_map[$node->getId()]);
    }

    public function getItemList() : array
    {
        return $this->item_list;
    }

    public function getNodeFromPath(string $path = "/") : ?ilCloudFileNode
    {
        if (!$this->isCaseSensitive() || $this->item_list[$path]) {
            return $this->item_list[$path];
        }

        foreach (array_keys($this->item_list) as $item) {
            if (strtolower($item) == strtolower($path)) {
                return $this->item_list[$item];
            }
        }

        return null;
    }

    public function getNodeFromId(int $id) : bool|ilCloudFileNode
    {
        return $this->item_list[$this->id_to_path_map[$id]];
    }

    /**
     * @throws ilCloudException
     */
    public function setLoadingOfFolderComplete(string $path) : void
    {
        $node = $this->getNodeFromPath($path);
        if (!$node) {
            throw new ilCloudException(ilCloudException::PATH_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION, $path);
        }
        $node->setLoadingComplete(true);
    }

    public function updateFileTree(string $current_path) : void
    {
        $node = $this->getNodeFromPath($current_path);

        if (!$node) {
            $this->updateFileTree(dirname($current_path));
            $node = $this->getNodeFromPath($current_path);
        }
        if (!$node->getLoadingComplete()) {
            $this->addItemsFromService($node->getId());
        }
        $this->storeFileTreeToSession();
    }

    /**
     * @throws ilCloudException
     */
    public function addItemsFromService(int $folder_id) : void
    {
        try {
            $node = $this->getNodeFromId($folder_id);
            if (!$node) {
                throw new ilCloudException(ilCloudException::ID_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION, $folder_id);
            }
            $service = ilCloudConnector::getServiceClass($this->getServiceName(), $this->getId());
            if (!$service->addToFileTreeWithId($this, $node->getId())) {
                $service->addToFileTree($this, $node->getPath());
            }
        } catch (Exception $e) {
            if ($e instanceof ilCloudException) {
                throw $e;
            }
            throw new ilCloudException(ilCloudException::ADD_ITEMS_FROM_SERVICE_FAILED, $e->getMessage());
        }
    }

    /**
     * @throws ilCloudException
     */
    public function addFolderToService(int $id, string $folder_name) : bool|ilCloudFileNode|null
    {
        try {
            if ($folder_name == null) {
                throw new ilCloudException(ilCloudException::INVALID_INPUT, $folder_name);
            }
            $current_node = $this->getNodeFromId($id);
            $path = ilCloudUtil::joinPaths($current_node->getPath(), ilCloudUtil::normalizePath($folder_name));

            if ($this->getNodeFromPath($path) != null) {
                throw new ilCloudException(ilCloudException::FOLDER_ALREADY_EXISTING_ON_SERVICE, $folder_name);
            }

            $current_node->setLoadingComplete(false);
            $this->storeFileTreeToSession();

            $service = ilCloudConnector::getServiceClass($this->getServiceName(), $this->getId());

            $new_folder_id = $service->createFolderById($id, $folder_name);
            $new_node = null;

            if (is_null($new_folder_id) || !$new_folder_id) {
                // Use path
                $service->createFolder($path, $this);
                $this->addItemsFromService($current_node->getId());
                $new_path = ilCloudUtil::joinPaths($current_node->getPath(), $folder_name);
                $new_node = $this->getNodeFromPath($new_path);
            } else {
                // Use id
                $this->addItemsFromService($current_node->getId());
                $new_node = $this->getNodeFromId($new_folder_id);
            }

            return $new_node;
        } catch (Exception $e) {
            if ($e instanceof ilCloudException) {
                throw $e;
            }
            throw new ilCloudException(ilCloudException::FOLDER_CREATION_FAILED, $e->getMessage());
        }
    }

    /**
     * @throws ilCloudException
     */
    public function uploadFileToService(int $current_id, string $tmp_name, string $file_name) : void
    {
        $plugin = ilCloudConnector::getPluginClass($this->getServiceName(), $this->getId());
        $max_file_size = $plugin->getMaxFileSize();

        if ($max_file_size >= filesize($tmp_name) / (1024 * 1024)) {
            $current_node = $this->getNodeFromId($current_id);

            $current_node->setLoadingComplete(false);
            $this->storeFileTreeToSession();

            try {
                $service = ilCloudConnector::getServiceClass($this->getServiceName(), $this->getId());
                if (!$service->putFileById($tmp_name, $file_name, $current_node->getId(), $this)) {
                    $service->putFile($tmp_name, $file_name, $current_node->getPath(), $this);
                }
            } catch (Exception $e) {
                if ($e instanceof ilCloudException) {
                    throw $e;
                }
                throw new ilCloudException(ilCloudException::UPLOAD_FAILED, $e->getMessage());
            }
        } else {
            throw new ilCloudException(ilCloudException::UPLOAD_FAILED_MAX_FILESIZE,
                filesize($tmp_name) / (1024 * 1024) . " MB");
        }
    }

    /**
     * @throws ilCloudException
     */
    public function deleteFromService(int $id) : void
    {
        $item_node = $this->getNodeFromId($id);

        try {
            $service = ilCloudConnector::getServiceClass($this->getServiceName(), $this->getId());

            if (!$service->deleteItemById($item_node->getId())) {
                $service->deleteItem($item_node->getPath(), $this);
            }

            $this->removeNode($item_node->getPath());
            $this->storeFileTreeToSession();
        } catch (Exception $e) {
            if ($e instanceof ilCloudException) {
                throw $e;
            }
            throw new ilCloudException(ilCloudException::DELETE_FAILED, $e->getMessage());
        }
    }

    /**
     * @throws ilCloudException
     */
    public function downloadFromService(int $id) : ilCloudException
    {
        try {
            $service = ilCloudConnector::getServiceClass($this->getServiceName(), $this->getId());
            $node = $this->getNodeFromId($id);

            if (!$service->getFileById($node->getId())) {
                $service->getFile($node->getPath(), $this);
            }
        } catch (Exception $e) {
            if ($e instanceof ilCloudException) {
                throw $e;
            }
            throw new ilCloudException(ilCloudException::DOWNLOAD_FAILED, $e->getMessage());
        }
    }

    public function storeFileTreeToSession()
    {
        $_SESSION['ilCloudFileTree'] = serialize($this);
    }

    public static function getFileTreeFromSession() : ilCloudFileTree|bool
    {
        if (isset($_SESSION['ilCloudFileTree'])) {
            return unserialize($_SESSION['ilCloudFileTree'], ['allowed_classes' => 'ilCloudFileTree']);
        } else {
            return false;
        }
    }

    public static function clearFileTreeSession() : void
    {
        $_SESSION['ilCloudFileTree'] = null;
    }

    public function orderListAlphabet(string $path1, string $path2) : int
    {
        $node1 = $this->getNodeFromPath($path1);
        $node2 = $this->getNodeFromPath($path2);
        if ($node1->getIsDir() != $node2->getIsDir()) {
            return $node2->getIsDir() ? +1 : -1;
        }
        $nameNode1 = strtolower(basename($node1->getPath()));
        $nameNode2 = strtolower(basename($node2->getPath()));
        return ($nameNode1 > $nameNode2) ? +1 : -1;
    }

    public function getSortedListOfChildren(ilCloudFileNode $node) : ?array
    {
        $children = $node->getChildrenPathes();
        usort($children, array("ilCloudFileTree", "orderListAlphabet"));
        return $children;
    }

    /**
     * @return string[]
     */
    public function getListForJSONEncode() : array
    {
        $list = array();
        foreach ($this->getItemList() as $path => $node) {
            $list[$node->getId()] = $node->getJSONEncode();
        }
        return $list;
    }
}
