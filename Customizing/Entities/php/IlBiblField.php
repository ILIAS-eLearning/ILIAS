<?php



/**
 * IlBiblField
 */
class IlBiblField
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var bool
     */
    private $dataType;

    /**
     * @var int|null
     */
    private $position;

    /**
     * @var bool
     */
    private $isStandardField;

    /**
     * @var int
     */
    private $objectId;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set identifier.
     *
     * @param string $identifier
     *
     * @return IlBiblField
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set dataType.
     *
     * @param bool $dataType
     *
     * @return IlBiblField
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType.
     *
     * @return bool
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set position.
     *
     * @param int|null $position
     *
     * @return IlBiblField
     */
    public function setPosition($position = null)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set isStandardField.
     *
     * @param bool $isStandardField
     *
     * @return IlBiblField
     */
    public function setIsStandardField($isStandardField)
    {
        $this->isStandardField = $isStandardField;

        return $this;
    }

    /**
     * Get isStandardField.
     *
     * @return bool
     */
    public function getIsStandardField()
    {
        return $this->isStandardField;
    }

    /**
     * Set objectId.
     *
     * @param int $objectId
     *
     * @return IlBiblField
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId.
     *
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }
}
