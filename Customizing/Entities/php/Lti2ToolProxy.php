<?php



/**
 * Lti2ToolProxy
 */
class Lti2ToolProxy
{
    /**
     * @var int
     */
    private $toolProxyPk = '0';

    /**
     * @var string
     */
    private $toolProxyId = '';

    /**
     * @var int
     */
    private $consumerPk = '0';

    /**
     * @var string
     */
    private $toolProxy;

    /**
     * @var \DateTime
     */
    private $created = '1970-01-01 00:00:00';

    /**
     * @var \DateTime
     */
    private $updated = '1970-01-01 00:00:00';


    /**
     * Get toolProxyPk.
     *
     * @return int
     */
    public function getToolProxyPk()
    {
        return $this->toolProxyPk;
    }

    /**
     * Set toolProxyId.
     *
     * @param string $toolProxyId
     *
     * @return Lti2ToolProxy
     */
    public function setToolProxyId($toolProxyId)
    {
        $this->toolProxyId = $toolProxyId;

        return $this;
    }

    /**
     * Get toolProxyId.
     *
     * @return string
     */
    public function getToolProxyId()
    {
        return $this->toolProxyId;
    }

    /**
     * Set consumerPk.
     *
     * @param int $consumerPk
     *
     * @return Lti2ToolProxy
     */
    public function setConsumerPk($consumerPk)
    {
        $this->consumerPk = $consumerPk;

        return $this;
    }

    /**
     * Get consumerPk.
     *
     * @return int
     */
    public function getConsumerPk()
    {
        return $this->consumerPk;
    }

    /**
     * Set toolProxy.
     *
     * @param string $toolProxy
     *
     * @return Lti2ToolProxy
     */
    public function setToolProxy($toolProxy)
    {
        $this->toolProxy = $toolProxy;

        return $this;
    }

    /**
     * Get toolProxy.
     *
     * @return string
     */
    public function getToolProxy()
    {
        return $this->toolProxy;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Lti2ToolProxy
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Lti2ToolProxy
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
