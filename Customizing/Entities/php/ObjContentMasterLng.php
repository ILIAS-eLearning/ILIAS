<?php



/**
 * ObjContentMasterLng
 */
class ObjContentMasterLng
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $masterLang = '';


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
     * Set masterLang.
     *
     * @param string $masterLang
     *
     * @return ObjContentMasterLng
     */
    public function setMasterLang($masterLang)
    {
        $this->masterLang = $masterLang;

        return $this;
    }

    /**
     * Get masterLang.
     *
     * @return string
     */
    public function getMasterLang()
    {
        return $this->masterLang;
    }
}
