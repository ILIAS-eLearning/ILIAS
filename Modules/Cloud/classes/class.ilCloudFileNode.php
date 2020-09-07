<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilCloudFileTree class
 *
 * Representation of a node (a file or a folder) in the file tree
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 * @ingroup ModulesCloud
 */
class ilCloudFileNode
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $path = "";

    /**
     * @var int
     */
    protected $parent_id = -1;

    /**
     * @var array
     */
    protected $children = array();

    /**
     * @var bool
     */
    protected $loading_complete = false;

    /**
     * @var bool
     */
    protected $is_dir = false;

    /**
     * @var int
     */
    protected $size = 0;

    /**
     * @var int
     */
    protected $modified = 0;

    /**
     * @var int
     */
    protected $created = 0;

    /**
     * @var string
     */
    protected $icon_path = "";

    /**
     * @var mixed
     */
    protected $mixed;

    /**
     * @param string $path
     */
    public function __construct($path, $id)
    {
        $this->setPath($path);
        $this->setId($id);
    }

    /**
     * @param $id
     */
    public function setId($id)
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
     * @param bool $complete
     */
    public function setLoadingComplete($complete)
    {
        $this->loading_complete = $complete;
    }

    /**
     * @return bool
     */
    public function getLoadingComplete()
    {
        return $this->loading_complete;
    }

    /**
     * @param string $path
     */
    public function setPath($path = "/")
    {
        $this->path = ilCloudUtil::normalizePath($path, $this->is_dir);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $path
     */
    public function addChild($path)
    {
        if (!isset($this->children[$path])) {
            $this->children[$path] = $path;
        }
    }

    /**
     * @param $path
     */
    public function removeChild($path)
    {
        if (isset($this->children[$path])) {
            unset($this->children[$path]);
        }
    }

    /**
     * @return array|null
     */
    public function getChildrenPathes()
    {
        if ($this->hasChildren()) {
            return $this->children;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return (count($this->children) > 0);
    }


    /**
     * @param $id
     */
    public function setParentId($id)
    {
        $this->parent_id = $id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param $is_dir
     */
    public function setIsDir($is_dir)
    {
        $this->is_dir = $is_dir;
    }

    /**
     * @return bool
     */
    public function getIsDir()
    {
        return $this->is_dir;
    }

    /**
     * @param $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * @return int
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param $path
     */
    public function setIconPath($path)
    {
        $this->icon_path = $path;
    }

    /**
     * @return string
     */
    public function getIconPath()
    {
        return $this->icon_path;
    }

    /**
     * @param mixed $mixed
     */
    public function setMixed($mixed)
    {
        $this->mixed = $mixed;
    }

    /**
     * @return mixed
     */
    public function getMixed()
    {
        return $this->mixed;
    }


    /**
     * @return array
     */
    public function getJSONEncode()
    {
        $node = array();
        $node["id"] = $this->getId();
        $node["is_dir"] = $this->getIsDir();
        $node["path"] = $this->getPath();
        $node["parent_id"] = $this->getParentId();
        $node["loading_complete"] = $this->getLoadingComplete();
        $node["children"] = $this->getChildrenPathes();
        $node["size"] = $this->getSize();

        return $node;
    }
}
