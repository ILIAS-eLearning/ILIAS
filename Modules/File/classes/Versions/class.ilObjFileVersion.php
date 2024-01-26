<?php

/**
 * Class ilObjFileVersion
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileVersion extends ArrayObject
{
    /**
     * @inheritDoc
     */
    public function __construct($input = array())
    {
        parent::__construct($input);
        foreach ($input as $k => $v) {
            $this->{$k} = $v;
        }
    }

    /**
     * @inheritDoc
     */
    public function getArrayCopy()
    {
        $a = [];
        $r = new ReflectionClass($this);
        foreach ($r->getProperties() as $p) {
            $p->setAccessible(true);
            $a[$p->getName()] = $p->getValue($this);
        }
        return $a;
    }

    protected $date = '';
    protected $user_id = 0;
    protected $obj_id = 0;
    protected $obj_type = '';
    protected $action = '';
    protected $info_params = '';
    protected $user_comment = '';
    protected $hist_entry_id = 1;
    protected $title = '';
    protected $filename = '';
    protected $version = '';
    protected $max_version = '';
    protected $rollback_version = '';
    protected $rollback_user_id = '';
    protected $size = 0;

    /**
     * @inheritDoc
     */
    public function offsetGet($index)
    {
        return $this->{$index};
    }

    /**
     * @return string
     */
    public function getDate() : string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return ilObjFileVersion
     */
    public function setDate(string $date) : ilObjFileVersion
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return ilObjFileVersion
     */
    public function setUserId(int $user_id) : ilObjFileVersion
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @param int $obj_id
     * @return ilObjFileVersion
     */
    public function setObjId(int $obj_id) : ilObjFileVersion
    {
        $this->obj_id = $obj_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjType() : string
    {
        return $this->obj_type;
    }

    /**
     * @param string $obj_type
     * @return ilObjFileVersion
     */
    public function setObjType(string $obj_type) : ilObjFileVersion
    {
        $this->obj_type = $obj_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction() : string
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return ilObjFileVersion
     */
    public function setAction(string $action) : ilObjFileVersion
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getInfoParams() : string
    {
        return $this->info_params;
    }

    /**
     * @param string $info_params
     * @return ilObjFileVersion
     */
    public function setInfoParams(string $info_params) : ilObjFileVersion
    {
        $this->info_params = $info_params;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserComment() : string
    {
        return $this->user_comment;
    }

    /**
     * @param string $user_comment
     * @return ilObjFileVersion
     */
    public function setUserComment(string $user_comment) : ilObjFileVersion
    {
        $this->user_comment = $user_comment;
        return $this;
    }

    /**
     * @return int
     */
    public function getHistEntryId() : int
    {
        return $this->hist_entry_id;
    }

    /**
     * @param int $hist_entry_id
     * @return ilObjFileVersion
     */
    public function setHistEntryId(int $hist_entry_id) : ilObjFileVersion
    {
        $this->hist_entry_id = $hist_entry_id;
        return $this;
    }

    /**
     * @return null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return ilObjFileVersion
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename() : string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return ilObjFileVersion
     */
    public function setFilename(string $filename) : ilObjFileVersion
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return ilObjFileVersion
     */
    public function setVersion(string $version) : ilObjFileVersion
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaxVersion() : string
    {
        return $this->max_version;
    }

    /**
     * @param string $max_version
     * @return ilObjFileVersion
     */
    public function setMaxVersion(string $max_version) : ilObjFileVersion
    {
        $this->max_version = $max_version;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize() : int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return ilObjFileVersion
     */
    public function setSize(int $size) : ilObjFileVersion
    {
        $this->size = $size;
        return $this;
    }
}
