<?php



/**
 * SearchCommandQueue
 */
class SearchCommandQueue
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $objType = '';

    /**
     * @var int
     */
    private $subId = '0';

    /**
     * @var string|null
     */
    private $subType;

    /**
     * @var string|null
     */
    private $command;

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;

    /**
     * @var bool
     */
    private $finished = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return SearchCommandQueue
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

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
     * Set objType.
     *
     * @param string $objType
     *
     * @return SearchCommandQueue
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set subId.
     *
     * @param int $subId
     *
     * @return SearchCommandQueue
     */
    public function setSubId($subId)
    {
        $this->subId = $subId;

        return $this;
    }

    /**
     * Get subId.
     *
     * @return int
     */
    public function getSubId()
    {
        return $this->subId;
    }

    /**
     * Set subType.
     *
     * @param string|null $subType
     *
     * @return SearchCommandQueue
     */
    public function setSubType($subType = null)
    {
        $this->subType = $subType;

        return $this;
    }

    /**
     * Get subType.
     *
     * @return string|null
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * Set command.
     *
     * @param string|null $command
     *
     * @return SearchCommandQueue
     */
    public function setCommand($command = null)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get command.
     *
     * @return string|null
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set lastUpdate.
     *
     * @param \DateTime|null $lastUpdate
     *
     * @return SearchCommandQueue
     */
    public function setLastUpdate($lastUpdate = null)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate.
     *
     * @return \DateTime|null
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set finished.
     *
     * @param bool $finished
     *
     * @return SearchCommandQueue
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * Get finished.
     *
     * @return bool
     */
    public function getFinished()
    {
        return $this->finished;
    }
}
