<?php



/**
 * IlMetaMetaData
 */
class IlMetaMetaData
{
    /**
     * @var int
     */
    private $metaMetaDataId = '0';

    /**
     * @var int|null
     */
    private $rbacId;

    /**
     * @var int|null
     */
    private $objId;

    /**
     * @var string|null
     */
    private $objType;

    /**
     * @var string|null
     */
    private $metaDataScheme;

    /**
     * @var string|null
     */
    private $language;


    /**
     * Get metaMetaDataId.
     *
     * @return int
     */
    public function getMetaMetaDataId()
    {
        return $this->metaMetaDataId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaMetaData
     */
    public function setRbacId($rbacId = null)
    {
        $this->rbacId = $rbacId;

        return $this;
    }

    /**
     * Get rbacId.
     *
     * @return int|null
     */
    public function getRbacId()
    {
        return $this->rbacId;
    }

    /**
     * Set objId.
     *
     * @param int|null $objId
     *
     * @return IlMetaMetaData
     */
    public function setObjId($objId = null)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int|null
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set objType.
     *
     * @param string|null $objType
     *
     * @return IlMetaMetaData
     */
    public function setObjType($objType = null)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string|null
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set metaDataScheme.
     *
     * @param string|null $metaDataScheme
     *
     * @return IlMetaMetaData
     */
    public function setMetaDataScheme($metaDataScheme = null)
    {
        $this->metaDataScheme = $metaDataScheme;

        return $this;
    }

    /**
     * Get metaDataScheme.
     *
     * @return string|null
     */
    public function getMetaDataScheme()
    {
        return $this->metaDataScheme;
    }

    /**
     * Set language.
     *
     * @param string|null $language
     *
     * @return IlMetaMetaData
     */
    public function setLanguage($language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
