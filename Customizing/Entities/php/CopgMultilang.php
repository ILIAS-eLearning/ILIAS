<?php



/**
 * CopgMultilang
 */
class CopgMultilang
{
    /**
     * @var string
     */
    private $parentType = '0';

    /**
     * @var int
     */
    private $parentId = '0';

    /**
     * @var string
     */
    private $masterLang = '';


    /**
     * Set parentType.
     *
     * @param string $parentType
     *
     * @return CopgMultilang
     */
    public function setParentType($parentType)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parentType.
     *
     * @return string
     */
    public function getParentType()
    {
        return $this->parentType;
    }

    /**
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return CopgMultilang
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set masterLang.
     *
     * @param string $masterLang
     *
     * @return CopgMultilang
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
