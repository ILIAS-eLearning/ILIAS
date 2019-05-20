<?php



/**
 * EcsDataMapping
 */
class EcsDataMapping
{
    /**
     * @var int
     */
    private $sid = '0';

    /**
     * @var bool
     */
    private $mappingType = '0';

    /**
     * @var string
     */
    private $ecsField = '';

    /**
     * @var int
     */
    private $advmdId = '0';


    /**
     * Set sid.
     *
     * @param int $sid
     *
     * @return EcsDataMapping
     */
    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get sid.
     *
     * @return int
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set mappingType.
     *
     * @param bool $mappingType
     *
     * @return EcsDataMapping
     */
    public function setMappingType($mappingType)
    {
        $this->mappingType = $mappingType;

        return $this;
    }

    /**
     * Get mappingType.
     *
     * @return bool
     */
    public function getMappingType()
    {
        return $this->mappingType;
    }

    /**
     * Set ecsField.
     *
     * @param string $ecsField
     *
     * @return EcsDataMapping
     */
    public function setEcsField($ecsField)
    {
        $this->ecsField = $ecsField;

        return $this;
    }

    /**
     * Get ecsField.
     *
     * @return string
     */
    public function getEcsField()
    {
        return $this->ecsField;
    }

    /**
     * Set advmdId.
     *
     * @param int $advmdId
     *
     * @return EcsDataMapping
     */
    public function setAdvmdId($advmdId)
    {
        $this->advmdId = $advmdId;

        return $this;
    }

    /**
     * Get advmdId.
     *
     * @return int
     */
    public function getAdvmdId()
    {
        return $this->advmdId;
    }
}
