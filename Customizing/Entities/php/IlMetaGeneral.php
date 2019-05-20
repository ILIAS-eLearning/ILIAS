<?php



/**
 * IlMetaGeneral
 */
class IlMetaGeneral
{
    /**
     * @var int
     */
    private $metaGeneralId = '0';

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
    private $generalStructure;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $titleLanguage;

    /**
     * @var string|null
     */
    private $coverage;

    /**
     * @var string|null
     */
    private $coverageLanguage;


    /**
     * Get metaGeneralId.
     *
     * @return int
     */
    public function getMetaGeneralId()
    {
        return $this->metaGeneralId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaGeneral
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
     * @return IlMetaGeneral
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
     * @return IlMetaGeneral
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
     * Set generalStructure.
     *
     * @param string|null $generalStructure
     *
     * @return IlMetaGeneral
     */
    public function setGeneralStructure($generalStructure = null)
    {
        $this->generalStructure = $generalStructure;

        return $this;
    }

    /**
     * Get generalStructure.
     *
     * @return string|null
     */
    public function getGeneralStructure()
    {
        return $this->generalStructure;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlMetaGeneral
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set titleLanguage.
     *
     * @param string|null $titleLanguage
     *
     * @return IlMetaGeneral
     */
    public function setTitleLanguage($titleLanguage = null)
    {
        $this->titleLanguage = $titleLanguage;

        return $this;
    }

    /**
     * Get titleLanguage.
     *
     * @return string|null
     */
    public function getTitleLanguage()
    {
        return $this->titleLanguage;
    }

    /**
     * Set coverage.
     *
     * @param string|null $coverage
     *
     * @return IlMetaGeneral
     */
    public function setCoverage($coverage = null)
    {
        $this->coverage = $coverage;

        return $this;
    }

    /**
     * Get coverage.
     *
     * @return string|null
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * Set coverageLanguage.
     *
     * @param string|null $coverageLanguage
     *
     * @return IlMetaGeneral
     */
    public function setCoverageLanguage($coverageLanguage = null)
    {
        $this->coverageLanguage = $coverageLanguage;

        return $this;
    }

    /**
     * Get coverageLanguage.
     *
     * @return string|null
     */
    public function getCoverageLanguage()
    {
        return $this->coverageLanguage;
    }
}
