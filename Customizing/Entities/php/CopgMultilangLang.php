<?php



/**
 * CopgMultilangLang
 */
class CopgMultilangLang
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
    private $lang = '';


    /**
     * Set parentType.
     *
     * @param string $parentType
     *
     * @return CopgMultilangLang
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
     * @return CopgMultilangLang
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
     * Set lang.
     *
     * @param string $lang
     *
     * @return CopgMultilangLang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }
}
