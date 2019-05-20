<?php



/**
 * SahsSc13SeqMapinfo
 */
class SahsSc13SeqMapinfo
{
    /**
     * @var int
     */
    private $seqnodeid = '0';

    /**
     * @var bool|null
     */
    private $readnormalizedmeasure;

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
    private $writenormalizedmeasure;

    /**
     * @var bool|null
     */
    private $writesatisfiedstatus;


    /**
     * Get seqnodeid.
     *
     * @return int
     */
    public function getSeqnodeid()
    {
        return $this->seqnodeid;
    }

    /**
     * Set readnormalizedmeasure.
     *
     * @param bool|null $readnormalizedmeasure
     *
     * @return SahsSc13SeqMapinfo
     */
    public function setReadnormalizedmeasure($readnormalizedmeasure = null)
    {
        $this->readnormalizedmeasure = $readnormalizedmeasure;

        return $this;
    }

    /**
     * Get readnormalizedmeasure.
     *
     * @return bool|null
     */
    public function getReadnormalizedmeasure()
    {
        return $this->readnormalizedmeasure;
    }

    /**
     * Set readsatisfiedstatus.
     *
     * @param bool|null $readsatisfiedstatus
     *
     * @return SahsSc13SeqMapinfo
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
     * @return SahsSc13SeqMapinfo
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
     * Set writenormalizedmeasure.
     *
     * @param bool|null $writenormalizedmeasure
     *
     * @return SahsSc13SeqMapinfo
     */
    public function setWritenormalizedmeasure($writenormalizedmeasure = null)
    {
        $this->writenormalizedmeasure = $writenormalizedmeasure;

        return $this;
    }

    /**
     * Get writenormalizedmeasure.
     *
     * @return bool|null
     */
    public function getWritenormalizedmeasure()
    {
        return $this->writenormalizedmeasure;
    }

    /**
     * Set writesatisfiedstatus.
     *
     * @param bool|null $writesatisfiedstatus
     *
     * @return SahsSc13SeqMapinfo
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
