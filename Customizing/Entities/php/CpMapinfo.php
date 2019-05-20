<?php



/**
 * CpMapinfo
 */
class CpMapinfo
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var bool|null
     */
    private $readnormalmeasure;

    /**
     * @var bool|null
     */
    private $readsatisfiedstatus;

    /**
     * @var string|null
     */
    private $targetobjectiveid;

    /**
     * @var bool|null
     */
    private $writenormalmeasure;

    /**
     * @var bool|null
     */
    private $writesatisfiedstatus;


    /**
     * Get cpNodeId.
     *
     * @return int
     */
    public function getCpNodeId()
    {
        return $this->cpNodeId;
    }

    /**
     * Set readnormalmeasure.
     *
     * @param bool|null $readnormalmeasure
     *
     * @return CpMapinfo
     */
    public function setReadnormalmeasure($readnormalmeasure = null)
    {
        $this->readnormalmeasure = $readnormalmeasure;

        return $this;
    }

    /**
     * Get readnormalmeasure.
     *
     * @return bool|null
     */
    public function getReadnormalmeasure()
    {
        return $this->readnormalmeasure;
    }

    /**
     * Set readsatisfiedstatus.
     *
     * @param bool|null $readsatisfiedstatus
     *
     * @return CpMapinfo
     */
    public function setReadsatisfiedstatus($readsatisfiedstatus = null)
    {
        $this->readsatisfiedstatus = $readsatisfiedstatus;

        return $this;
    }

    /**
     * Get readsatisfiedstatus.
     *
     * @return bool|null
     */
    public function getReadsatisfiedstatus()
    {
        return $this->readsatisfiedstatus;
    }

    /**
     * Set targetobjectiveid.
     *
     * @param string|null $targetobjectiveid
     *
     * @return CpMapinfo
     */
    public function setTargetobjectiveid($targetobjectiveid = null)
    {
        $this->targetobjectiveid = $targetobjectiveid;

        return $this;
    }

    /**
     * Get targetobjectiveid.
     *
     * @return string|null
     */
    public function getTargetobjectiveid()
    {
        return $this->targetobjectiveid;
    }

    /**
     * Set writenormalmeasure.
     *
     * @param bool|null $writenormalmeasure
     *
     * @return CpMapinfo
     */
    public function setWritenormalmeasure($writenormalmeasure = null)
    {
        $this->writenormalmeasure = $writenormalmeasure;

        return $this;
    }

    /**
     * Get writenormalmeasure.
     *
     * @return bool|null
     */
    public function getWritenormalmeasure()
    {
        return $this->writenormalmeasure;
    }

    /**
     * Set writesatisfiedstatus.
     *
     * @param bool|null $writesatisfiedstatus
     *
     * @return CpMapinfo
     */
    public function setWritesatisfiedstatus($writesatisfiedstatus = null)
    {
        $this->writesatisfiedstatus = $writesatisfiedstatus;

        return $this;
    }

    /**
     * Get writesatisfiedstatus.
     *
     * @return bool|null
     */
    public function getWritesatisfiedstatus()
    {
        return $this->writesatisfiedstatus;
    }
}
