<?php



/**
 * IlVerification
 */
class IlVerification
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string|null
     */
    private $parameters;

    /**
     * @var string|null
     */
    private $rawData;


    /**
     * Set id.
     *
     * @param int $id
     *
     * @return IlVerification
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set type.
     *
     * @param string $type
     *
     * @return IlVerification
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set parameters.
     *
     * @param string|null $parameters
     *
     * @return IlVerification
     */
    public function setParameters($parameters = null)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters.
     *
     * @return string|null
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set rawData.
     *
     * @param string|null $rawData
     *
     * @return IlVerification
     */
    public function setRawData($rawData = null)
    {
        $this->rawData = $rawData;

        return $this;
    }

    /**
     * Get rawData.
     *
     * @return string|null
     */
    public function getRawData()
    {
        return $this->rawData;
    }
}
