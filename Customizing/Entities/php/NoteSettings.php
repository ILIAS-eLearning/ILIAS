<?php



/**
 * NoteSettings
 */
class NoteSettings
{
    /**
     * @var int
     */
    private $repObjId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $objType = '-';

    /**
     * @var bool
     */
    private $activated = '0';


    /**
     * Set repObjId.
     *
     * @param int $repObjId
     *
     * @return NoteSettings
     */
    public function setRepObjId($repObjId)
    {
        $this->repObjId = $repObjId;

        return $this;
    }

    /**
     * Get repObjId.
     *
     * @return int
     */
    public function getRepObjId()
    {
        return $this->repObjId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return NoteSettings
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set objType.
     *
     * @param string $objType
     *
     * @return NoteSettings
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set activated.
     *
     * @param bool $activated
     *
     * @return NoteSettings
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get activated.
     *
     * @return bool
     */
    public function getActivated()
    {
        return $this->activated;
    }
}
