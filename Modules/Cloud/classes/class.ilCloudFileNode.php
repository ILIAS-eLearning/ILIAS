<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilCloudFileTree class
 * Representation of a node (a file or a folder) in the file tree
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @ingroup ModulesCloud
 */
class ilCloudFileNode
{
    protected int $id = 0;
    protected string $path = "";
    protected int $parent_id = -1;
    protected array $children = array();
    protected bool $loading_complete = false;
    protected bool $is_dir = false;
    protected int $size = 0;
    protected int $modified = 0;
    protected int $created = 0;
    protected string $icon_path = "";
    protected mixed $mixed;

    public function __construct(string $path, int $id)
    {
        $this->setPath($path);
        $this->setId($id);
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setLoadingComplete(bool $complete)
    {
        $this->loading_complete = $complete;
    }

    public function getLoadingComplete() : bool
    {
        return $this->loading_complete;
    }

    public function setPath(string $path = "/") : void
    {
        $this->path = ilCloudUtil::normalizePath($path, $this->is_dir);
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function addChild(string $path)
    {
        if (!isset($this->children[$path])) {
            $this->children[$path] = $path;
        }
    }

    public function removeChild(string $path)
    {
        if (isset($this->children[$path])) {
            unset($this->children[$path]);
        }
    }

    public function getChildrenPathes() : ?array
    {
        if ($this->hasChildren()) {
            return $this->children;
        }

        return null;
    }

    public function hasChildren() : bool
    {
        return (count($this->children) > 0);
    }

    public function setParentId(int $id)
    {
        $this->parent_id = $id;
    }

    public function getParentId() : int
    {
        return $this->parent_id;
    }

    public function setIsDir(bool $is_dir)
    {
        $this->is_dir = $is_dir;
    }

    public function getIsDir() : bool
    {
        return $this->is_dir;
    }

    public function setSize(int $size)
    {
        $this->size = $size;
    }

    public function getSize() : int
    {
        return $this->size;
    }

    public function setModified(bool $modified) : void
    {
        $this->modified = $modified;
    }

    public function getModified() : bool
    {
        return $this->modified;
    }

    public function setIconPath(string $path) : void
    {
        $this->icon_path = $path;
    }

    public function getIconPath() : string
    {
        return $this->icon_path;
    }

    public function setMixed(string $mixed)
    {
        $this->mixed = $mixed;
    }

    public function getMixed() : string
    {
        return $this->mixed;
    }

    public function getJSONEncode() : array
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
