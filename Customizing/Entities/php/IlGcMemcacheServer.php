<?php



/**
 * IlGcMemcacheServer
 */
class IlGcMemcacheServer
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool|null
     */
    private $status;

    /**
     * @var string|null
     */
    private $host;

    /**
     * @var int|null
     */
    private $port;

    /**
     * @var int|null
     */
    private $weight;

    /**
     * @var bool|null
     */
    private $flushNeeded;


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
     * Set status.
     *
     * @param bool|null $status
     *
     * @return IlGcMemcacheServer
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set host.
     *
     * @param string|null $host
     *
     * @return IlGcMemcacheServer
     */
    public function setHost($host = null)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host.
     *
     * @return string|null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set port.
     *
     * @param int|null $port
     *
     * @return IlGcMemcacheServer
     */
    public function setPort($port = null)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port.
     *
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set weight.
     *
     * @param int|null $weight
     *
     * @return IlGcMemcacheServer
     */
    public function setWeight($weight = null)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight.
     *
     * @return int|null
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set flushNeeded.
     *
     * @param bool|null $flushNeeded
     *
     * @return IlGcMemcacheServer
     */
    public function setFlushNeeded($flushNeeded = null)
    {
        $this->flushNeeded = $flushNeeded;

        return $this;
    }

    /**
     * Get flushNeeded.
     *
     * @return bool|null
     */
    public function getFlushNeeded()
    {
        return $this->flushNeeded;
    }
}
