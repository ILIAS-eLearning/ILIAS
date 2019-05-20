<?php



/**
 * SvyMaterial
 */
class SvyMaterial
{
    /**
     * @var int
     */
    private $materialId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $internalLink;

    /**
     * @var string|null
     */
    private $importId;

    /**
     * @var string|null
     */
    private $materialTitle;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $textMaterial;

    /**
     * @var string|null
     */
    private $externalLink;

    /**
     * @var string|null
     */
    private $fileMaterial;

    /**
     * @var int|null
     */
    private $materialType = '0';


    /**
     * Get materialId.
     *
     * @return int
     */
    public function getMaterialId()
    {
        return $this->materialId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return SvyMaterial
     */
    public function setQuestionFi($questionFi)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set internalLink.
     *
     * @param string|null $internalLink
     *
     * @return SvyMaterial
     */
    public function setInternalLink($internalLink = null)
    {
        $this->internalLink = $internalLink;

        return $this;
    }

    /**
     * Get internalLink.
     *
     * @return string|null
     */
    public function getInternalLink()
    {
        return $this->internalLink;
    }

    /**
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return SvyMaterial
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return string|null
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set materialTitle.
     *
     * @param string|null $materialTitle
     *
     * @return SvyMaterial
     */
    public function setMaterialTitle($materialTitle = null)
    {
        $this->materialTitle = $materialTitle;

        return $this;
    }

    /**
     * Get materialTitle.
     *
     * @return string|null
     */
    public function getMaterialTitle()
    {
        return $this->materialTitle;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyMaterial
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set textMaterial.
     *
     * @param string|null $textMaterial
     *
     * @return SvyMaterial
     */
    public function setTextMaterial($textMaterial = null)
    {
        $this->textMaterial = $textMaterial;

        return $this;
    }

    /**
     * Get textMaterial.
     *
     * @return string|null
     */
    public function getTextMaterial()
    {
        return $this->textMaterial;
    }

    /**
     * Set externalLink.
     *
     * @param string|null $externalLink
     *
     * @return SvyMaterial
     */
    public function setExternalLink($externalLink = null)
    {
        $this->externalLink = $externalLink;

        return $this;
    }

    /**
     * Get externalLink.
     *
     * @return string|null
     */
    public function getExternalLink()
    {
        return $this->externalLink;
    }

    /**
     * Set fileMaterial.
     *
     * @param string|null $fileMaterial
     *
     * @return SvyMaterial
     */
    public function setFileMaterial($fileMaterial = null)
    {
        $this->fileMaterial = $fileMaterial;

        return $this;
    }

    /**
     * Get fileMaterial.
     *
     * @return string|null
     */
    public function getFileMaterial()
    {
        return $this->fileMaterial;
    }

    /**
     * Set materialType.
     *
     * @param int|null $materialType
     *
     * @return SvyMaterial
     */
    public function setMaterialType($materialType = null)
    {
        $this->materialType = $materialType;

        return $this;
    }

    /**
     * Get materialType.
     *
     * @return int|null
     */
    public function getMaterialType()
    {
        return $this->materialType;
    }
}
