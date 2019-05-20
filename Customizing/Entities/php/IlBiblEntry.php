<?php



/**
 * IlBiblEntry
 */
class IlBiblEntry
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $dataId;

    /**
     * @var string|null
     */
    private $type;


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
     * Set dataId.
     *
     * @param int|null $dataId
     *
     * @return IlBiblEntry
     */
    public function setDataId($dataId = null)
    {
        $this->dataId = $dataId;

        return $this;
    }

    /**
     * Get dataId.
     *
     * @return int|null
     */
    public function getDataId()
    {
        return $this->dataId;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return IlBiblEntry
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }
}
