<?php



/**
 * CrsGroupings
 */
class CrsGroupings
{
    /**
     * @var int
     */
    private $crsGrpId = '0';

    /**
     * @var int
     */
    private $crsRefId = '0';

    /**
     * @var int
     */
    private $crsId = '0';

    /**
     * @var string|null
     */
    private $uniqueField;


    /**
     * Get crsGrpId.
     *
     * @return int
     */
    public function getCrsGrpId()
    {
        return $this->crsGrpId;
    }

    /**
     * Set crsRefId.
     *
     * @param int $crsRefId
     *
     * @return CrsGroupings
     */
    public function setCrsRefId($crsRefId)
    {
        $this->crsRefId = $crsRefId;

        return $this;
    }

    /**
     * Get crsRefId.
     *
     * @return int
     */
    public function getCrsRefId()
    {
        return $this->crsRefId;
    }

    /**
     * Set crsId.
     *
     * @param int $crsId
     *
     * @return CrsGroupings
     */
    public function setCrsId($crsId)
    {
        $this->crsId = $crsId;

        return $this;
    }

    /**
     * Get crsId.
     *
     * @return int
     */
    public function getCrsId()
    {
        return $this->crsId;
    }

    /**
     * Set uniqueField.
     *
     * @param string|null $uniqueField
     *
     * @return CrsGroupings
     */
    public function setUniqueField($uniqueField = null)
    {
        $this->uniqueField = $uniqueField;

        return $this;
    }

    /**
     * Get uniqueField.
     *
     * @return string|null
     */
    public function getUniqueField()
    {
        return $this->uniqueField;
    }
}
