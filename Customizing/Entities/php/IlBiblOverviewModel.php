<?php



/**
 * IlBiblOverviewModel
 */
class IlBiblOverviewModel
{
    /**
     * @var int
     */
    private $ovmId = '0';

    /**
     * @var string|null
     */
    private $literatureType;

    /**
     * @var string|null
     */
    private $pattern;

    /**
     * @var int|null
     */
    private $fileTypeId;


    /**
     * Get ovmId.
     *
     * @return int
     */
    public function getOvmId()
    {
        return $this->ovmId;
    }

    /**
     * Set literatureType.
     *
     * @param string|null $literatureType
     *
     * @return IlBiblOverviewModel
     */
    public function setLiteratureType($literatureType = null)
    {
        $this->literatureType = $literatureType;

        return $this;
    }

    /**
     * Get literatureType.
     *
     * @return string|null
     */
    public function getLiteratureType()
    {
        return $this->literatureType;
    }

    /**
     * Set pattern.
     *
     * @param string|null $pattern
     *
     * @return IlBiblOverviewModel
     */
    public function setPattern($pattern = null)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get pattern.
     *
     * @return string|null
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set fileTypeId.
     *
     * @param int|null $fileTypeId
     *
     * @return IlBiblOverviewModel
     */
    public function setFileTypeId($fileTypeId = null)
    {
        $this->fileTypeId = $fileTypeId;

        return $this;
    }

    /**
     * Get fileTypeId.
     *
     * @return int|null
     */
    public function getFileTypeId()
    {
        return $this->fileTypeId;
    }
}
