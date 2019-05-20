<?php



/**
 * EcsEvents
 */
class EcsEvents
{
    /**
     * @var int
     */
    private $eventId = '0';

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $op;

    /**
     * @var int
     */
    private $serverId = '0';


    /**
     * Get eventId.
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return EcsEvents
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

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return EcsEvents
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
     * Set op.
     *
     * @param string|null $op
     *
     * @return EcsEvents
     */
    public function setOp($op = null)
    {
        $this->op = $op;

        return $this;
    }

    /**
     * Get op.
     *
     * @return string|null
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * Set serverId.
     *
     * @param int $serverId
     *
     * @return EcsEvents
     */
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;

        return $this;
    }

    /**
     * Get serverId.
     *
     * @return int
     */
    public function getServerId()
    {
        return $this->serverId;
    }
}
