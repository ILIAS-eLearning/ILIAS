<?php



/**
 * AiccObject
 */
class AiccObject
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $slmId = '0';

    /**
     * @var string|null
     */
    private $systemId;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $developerId;

    /**
     * @var string|null
     */
    private $cType;


    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set slmId.
     *
     * @param int $slmId
     *
     * @return AiccObject
     */
    public function setSlmId($slmId)
    {
        $this->slmId = $slmId;

        return $this;
    }

    /**
     * Get slmId.
     *
     * @return int
     */
    public function getSlmId()
    {
        return $this->slmId;
    }

    /**
     * Set systemId.
     *
     * @param string|null $systemId
     *
     * @return AiccObject
     */
    public function setSystemId($systemId = null)
    {
        $this->systemId = $systemId;

        return $this;
    }

    /**
     * Get systemId.
     *
     * @return string|null
     */
    public function getSystemId()
    {
        return $this->systemId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return AiccObject
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
     * Set description.
     *
     * @param string|null $description
     *
     * @return AiccObject
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set developerId.
     *
     * @param string|null $developerId
     *
     * @return AiccObject
     */
    public function setDeveloperId($developerId = null)
    {
        $this->developerId = $developerId;

        return $this;
    }

    /**
     * Get developerId.
     *
     * @return string|null
     */
    public function getDeveloperId()
    {
        return $this->developerId;
    }

    /**
     * Set cType.
     *
     * @param string|null $cType
     *
     * @return AiccObject
     */
    public function setCType($cType = null)
    {
        $this->cType = $cType;

        return $this;
    }

    /**
     * Get cType.
     *
     * @return string|null
     */
    public function getCType()
    {
        return $this->cType;
    }
}
