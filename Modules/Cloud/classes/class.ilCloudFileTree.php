<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Cloud/exceptions/class.ilCloudException.php");
include_once("class.ilCloudFileNode.php");
include_once("class.ilCloudConnector.php");
include_once("class.ilCloudUtil.php");

/**
 * ilCloudFileTree class
 *
 * Representation of the structure of all files and folders so far. Not really a tree but a list simulating a tree
 * (for faster access on the nodes). This class also calls the functions of a service to update the tree (addToFileTree,
 * deleteItem, etc.)
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 * @ingroup ModulesCloud
 */
class ilCloudFileTree
{

    /**
     * id of the ilCloudFileTree, equals the object_id of the calling object or gui_class
     * @var int id
     */
    protected $id = 0;

    /**
     * @var ilCloudFileNode
     */
    protected $root_node = null;

    /**
     * Path to $root_node ($root_node has always path "/", root_path is the path which can be changed in the settings)
     * @var string
     */
    protected $root_path = "";
    /**
     * @var array
     */
    protected $item_list = array();

    /**
     * Only for better performance
     */
    protected $id_to_path_map = array();

    /**
     * @var string $service_name
     */
    protected $service_name = "";

    /**
     * @var bool
     */
    protected $case_sensitive = false;

    /**
     * @param string $root_path
     * @param string $root_id
     * @param int $id
     * @param string $service_name
     * @param bool $case_sensitive
     */
    public function __construct($root_path = "/", $root_id = "root", $id, $service_name)
    {
        $this->setId($id);
        $this->root_node = $this->createNode($root_path, $root_id, true);
        $this->setServiceName($service_name);
        $service = ilCloudConnector::getServiceClass($service_name, $id);
        $this->setCaseSensitive($service->isCaseSensitive());
    }

    /**
     * @param int $id
     */
    protected function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $path
     */
    protected function setRootPath($path = "/")
    {
        $this->root_path = ilCloudUtil::normalizePath($path);
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        return $this->root_path;
    }

    /**
     * @param string $service_name
     */
    protected function setServiceName($service_name)
    {
        $this->service_name = $service_name;
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->service_name;
    }

    /**
     * @return boolean
     */
    public function isCaseSensitive()
    {
        return $this->case_sensitive;
    }

    /**
     * @param boolean $case_sensitive
     */
    public function setCaseSensitive($case_sensitive)
    {
        $this->case_sensitive = $case_sensitive;
    }

    /**
     * @return ilCloudFileNode|null
     */
    public function getRootNode()
    {
        return $this->root_node;
    }

    /**
     * @param string $path
     * @param bool $is_dir
     * @return ilCloudFileNode
     */
    protected function createNode($path = "/", $id, $is_dir = false)
    {
        $node                              = new ilCloudFileNode(ilCloudUtil::normalizePath($path),$id);
        $this->item_list[$node->getPath()] = $node;
        $this->id_to_path_map[$node->getId()] = $node->getPath();
        $node->setIsDir($is_dir);
        return $node;
    }

    /**
     * @param $path
     * @param $is_Dir
     * @param null $modified
     * @param int $size
     * @return ilCloudFileNode
     */
    public function addNode($path, $id,$is_Dir, $modified = null, $size = 0)
    {
        $path = ilCloudUtil::normalizePath($path);
        $node = $this->getNodeFromPath($path);

        //node does not yet exist
        if (!$node)
        {
            if ($this->getNodeFromId($id))
            {
                throw new ilCloudException(ilCloudException::ID_ALREADY_EXISTS_IN_FILE_TREE_IN_SESSION);
            }
            $path_of_parent = ilCloudUtil::normalizePath(dirname($path));
            $node_parent = $this->getNodeFromPath($path_of_parent);
            if(!$node_parent)
            {
                throw new ilCloudException(ilCloudException::PATH_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION, "Parent: ".$path_of_parent);
            }
            $node        = $this->createNode($path, $id, $is_Dir);
            $node->setParentId($node_parent->getId());
            $node_parent->addChild($node->getPath());
        }

        $node->setSize($size);
        $node->setModified($modified);

        return $node;
    }


    /**
     * Add node that relies on id's
     *
     * @param      $path
     * @param      $id
     * @param      $parent_id
     * @param      $is_Dir
     * @param null $modified
     * @param int  $size
     *
     * @return ilCloudFileNode
     * @throws ilCloudException
     */
    public function addIdBasedNode($path, $id, $parent_id, $is_Dir, $modified = null, $size = 0)
    {
        $path = ilCloudUtil::normalizePath($path);
        $node = $this->getNodeFromPath($path);

        //node does not yet exist
        if (!$node)
        {
            $nodeFromId = $this->getNodeFromId($id);
            // If path isn't found but id is there -> Path got changed
            if ($nodeFromId)
            {
                // Adjust path
                $nodeFromId->setPath($path);
            }

            $node_parent = $this->getNodeFromId($parent_id);
            if(!$node_parent)
            {
                throw new ilCloudException(ilCloudException::PATH_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION, "Parent: ". $parent_id);
            }
            $node        = $this->createNode($path, $id, $is_Dir);
            $node->setParentId($node_parent->getId());
            $node_parent->addChild($node->getPath());
        }

        $node->setSize($size);
        $node->setModified($modified);

        return $node;
    }


    /**
     * @param $path
     */
    public function removeNode($path)
    {
        $node   = $this->getNodeFromPath($path);
        $parent = $this->getNodeFromId($node->getParentId());
        $parent->removeChild($path);
        unset($this->item_list[$node->getPath()]);
        unset($this->id_to_path_map[$node->getId()]);
    }

    /**
     * @return array
     */
    public function getItemList()
    {
        return $this->item_list;
    }

    /**
     * @param   string $path
     * @return  ilCloudFileNode  node;
     */
    public function getNodeFromPath($path = "/")
    {
        if (!$this->isCaseSensitive() || $this->item_list[$path])
        {
            return $this->item_list[$path];
        }

        foreach (array_keys($this->item_list) as $item)
        {
            if (strtolower($item) == strtolower($path))
            {
                return $this->item_list[$item];
            }
        }

        return null;
    }

    /**
     * @param $id
     * @return bool|ilCloudFileNode
     */
    public function getNodeFromId($id)
    {
        return $this->item_list[$this->id_to_path_map[$id]];
    }

    /**
     * @param $path
     * @throws ilCloudException
     */
    public function setLoadingOfFolderComplete($path)
    {
        $node = $this->getNodeFromPath($path);
        if(!$node)
        {
            throw new ilCloudException(ilCloudException::PATH_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION,$path);
        }
        $node->setLoadingComplete(true);
    }

    /**
     * @param $current_path
     */
    public function updateFileTree($current_path)
    {
        $node = $this->getNodeFromPath($current_path);

        if (!$node)
        {
            $this->updateFileTree(dirname($current_path));
            $node = $this->getNodeFromPath($current_path);
        }
        if (!$node->getLoadingComplete())
        {
            $this->addItemsFromService($node->getId());
        }
        $this->storeFileTreeToSession();
    }


    /**
     * @param $folder_id
     *
     * @throws ilCloudException
     */
    public function addItemsFromService($folder_id)
    {
        try
        {
            $node = $this->getNodeFromId($folder_id);
            if(!$node)
            {
                throw new ilCloudException(ilCloudException::ID_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION,$folder_id);
            }
            $service = ilCloudConnector::getServiceClass($this->getServiceName(), $this->getId());
            if (!$service->addToFileTreeWithId($this, $node->getId())) {
                $service->addToFileTree($this, $node->getPath());
            }
        } catch (Exception $e)
        {
            if ($e instanceof ilCloudException)
            {
                throw $e;
            }
            throw new ilCloudException(ilCloudException::ADD_ITEMS_FROM_SERVICE_FAILED,$e->getMessage());
        }
    }


    /**
     * @param $id
     * @param $folder_name
     *
     * @return bool|ilCloudFileNode|null
     * @throws ilCloudException
     */
    public function addFolderToService($id, $folder_name)
    {
        try
        {
            if ($folder_name == null)
            {
                throw new ilCloudException(ilCloudException::INVALID_INPUT, $folder_name);
            }
            $current_node = $this->getNodeFromId($id);
            $path = ilCloudUtil::joinPaths($current_node->getPath(), ilCloudUtil::normalizePath($folder_name));

            if($this->getNodeFromPath($path) != null)
            {
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
        } catch (Exception $e)
        {
            if ($e instanceof ilCloudException)
            {
                throw $e;
            }
            throw new ilCloudException(ilCloudException::FOLDER_CREATION_FAILED, $e->getMessage());
        }

    }


    /**
     * @param $current_id
     * @param $tmp_name
     * @param $file_name
     *
     * @throws ilCloudException
     */
    public function uploadFileToService($current_id, $tmp_name, $file_name)
    {
        $plugin = ilCloudConnector::getPluginClass($this->getServiceName(), $this->getId());
        $max_file_size = $plugin->getMaxFileSize();

        if($max_file_size >= filesize($tmp_name)/(1024 * 1024))
        {
            $current_node = $this->getNodeFromId($current_id);

            $current_node->setLoadingComplete(false);
            $this->storeFileTreeToSession();

            try
            {
                $service = ilCloudConnector::getServiceClass($this->getServiceName(), $this->getId());
                if (!$service->putFileById($tmp_name, $file_name, $current_node->getId(), $this)) {
                    $service->putFile($tmp_name, $file_name, $current_node->getPath(), $this);
                }
            } catch (Exception $e)
            {
                if ($e instanceof ilCloudException)
                {
                    throw $e;
                }
                throw new ilCloudException(ilCloudException::UPLOAD_FAILED, $e->getMessage());
            }
        }
        else
        {
            throw new ilCloudException(ilCloudException::UPLOAD_FAILED_MAX_FILESIZE, filesize($tmp_name) / (1024 * 1024) . " MB");
        }
    }

    /**
     * @param $id
     * @throws ilCloudException
     */
    public function deleteFromService($id)
    {
        $item_node = $this->getNodeFromId($id);

        try
        {
            $service = ilCloudConnector::getServiceClass($this->getServiceName(), $this->getId());

            if (!$service->deleteItemById($item_node->getId())) {
                $service->deleteItem($item_node->getPath(), $this);
            }

            $this->removeNode($item_node->getPath());
            $this->storeFileTreeToSession();
        } catch (Exception $e)
        {
            if ($e instanceof ilCloudException)
            {
                throw $e;
            }
            throw new ilCloudException(ilCloudException::DELETE_FAILED, $e->getMessage());
        }
    }

    /**
     * @param $id
     * @throws ilCloudException
     */
    public function downloadFromService($id)
    {
        try
        {
            $service = ilCloudConnector::getServiceClass($this->getServiceName(), $this->getId());
            $node = $this->getNodeFromId($id);

            if (!$service->getFileById($node->getId())) {
                $service->getFile($node->getPath(), $this);
            }

        } catch (Exception $e)
        {
            if ($e instanceof ilCloudException)
            {
                throw $e;
            }
            throw new ilCloudException(ilCloudException::DOWNLOAD_FAILED, $e->getMessage());
        }
    }

    public function storeFileTreeToSession()
    {
        $_SESSION['ilCloudFileTree'] = null;
        $_SESSION['ilCloudFileTree'] = serialize($this);
    }

    /**
     * @return    ilCloudFileTree  fileTree;
     */
    public static function getFileTreeFromSession()
    {
        if(isset($_SESSION['ilCloudFileTree']))
        {
            return unserialize($_SESSION['ilCloudFileTree']);
        }
        else
        {
            return false;
        }

    }


    public static function clearFileTreeSession()
    {
        $_SESSION['ilCloudFileTree'] = null;
    }

    /**
     * @param $path1
     * @param $path2
     * @return int
     */
    public function orderListAlphabet($path1, $path2)
    {
        $node1 = $this->getNodeFromPath($path1);
        $node2 = $this->getNodeFromPath($path2);
        if ($node1->getIsDir() != $node2->getIsDir())
        {
            return $node2->getIsDir() ? +1 : -1;
        }
        $nameNode1 = strtolower(basename($node1->getPath()));
        $nameNode2 = strtolower(basename($node2->getPath()));
        return ($nameNode1 > $nameNode2) ? +1 : -1;
    }

    /**
     * @param ilCloudFileNode $node
     * @return array|null
     */
    public function getSortedListOfChildren(ilCloudFileNode $node)
    {
        $children = $node->getChildrenPathes();
        usort($children, array("ilCloudFileTree", "orderListAlphabet"));
        return $children;
    }

    /**
     * @return array
     */
    public function getListForJSONEncode()
    {
        $list = array();
        foreach ($this->getItemList() as $path => $node)
        {
            $list[$node->getId()] = $node->getJSONEncode();
        }
        return $list;
    }
}
?>
