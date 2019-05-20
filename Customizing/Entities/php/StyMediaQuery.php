<?php



/**
 * StyMediaQuery
 */
class StyMediaQuery
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $styleId = '0';

    /**
     * @var int
     */
    private $orderNr = '0';

    /**
     * @var string|null
     */
    private $mquery;


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
     * Set styleId.
     *
     * @param int $styleId
     *
     * @return StyMediaQuery
     */
    public function setStyleId($styleId)
    {
        $this->styleId = $styleId;

        return $this;
    }

    /**
     * Get styleId.
     *
     * @return int
     */
    public function getStyleId()
    {
        return $this->styleId;
    }

    /**
     * Set orderNr.
     *
     * @param int $orderNr
     *
     * @return StyMediaQuery
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get orderNr.
     *
     * @return int
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }

    /**
     * Set mquery.
     *
     * @param string|null $mquery
     *
     * @return StyMediaQuery
     */
    public function setMquery($mquery = null)
    {
        $this->mquery = $mquery;

        return $this;
    }

    /**
     * Get mquery.
     *
     * @return string|null
     */
    public function getMquery()
    {
        return $this->mquery;
    }
}
