<?php



/**
 * ObjNotiSettings
 */
class ObjNotiSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var bool
     */
    private $notiMode = '0';


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
     * Set notiMode.
     *
     * @param bool $notiMode
     *
     * @return ObjNotiSettings
     */
    public function setNotiMode($notiMode)
    {
        $this->notiMode = $notiMode;

        return $this;
    }

    /**
     * Get notiMode.
     *
     * @return bool
     */
    public function getNotiMode()
    {
        return $this->notiMode;
    }
}
