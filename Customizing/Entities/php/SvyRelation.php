<?php



/**
 * SvyRelation
 */
class SvyRelation
{
    /**
     * @var int
     */
    private $relationId = '0';

    /**
     * @var string|null
     */
    private $longname;

    /**
     * @var string|null
     */
    private $shortname;

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get relationId.
     *
     * @return int
     */
    public function getRelationId()
    {
        return $this->relationId;
    }

    /**
     * Set longname.
     *
     * @param string|null $longname
     *
     * @return SvyRelation
     */
    public function setLongname($longname = null)
    {
        $this->longname = $longname;

        return $this;
    }

    /**
     * Get longname.
     *
     * @return string|null
     */
    public function getLongname()
    {
        return $this->longname;
    }

    /**
     * Set shortname.
     *
     * @param string|null $shortname
     *
     * @return SvyRelation
     */
    public function setShortname($shortname = null)
    {
        $this->shortname = $shortname;

        return $this;
    }

    /**
     * Get shortname.
     *
     * @return string|null
     */
    public function getShortname()
    {
        return $this->shortname;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyRelation
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}
