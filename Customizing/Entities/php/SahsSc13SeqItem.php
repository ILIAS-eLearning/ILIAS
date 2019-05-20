<?php



/**
 * SahsSc13SeqItem
 */
class SahsSc13SeqItem
{
    /**
     * @var int
     */
    private $sahsSc13TreeNodeId = '0';

    /**
     * @var bool
     */
    private $rootlevel = '0';

    /**
     * @var string|null
     */
    private $importid;

    /**
     * @var int
     */
    private $seqnodeid = '0';

    /**
     * @var string|null
     */
    private $sequencingid;

    /**
     * @var bool|null
     */
    private $nocopy;

    /**
     * @var bool|null
     */
    private $nodelete;

    /**
     * @var bool|null
     */
    private $nomove;

    /**
     * @var string|null
     */
    private $seqxml;

    /**
     * @var string|null
     */
    private $importseqxml;


    /**
     * Set sahsSc13TreeNodeId.
     *
     * @param int $sahsSc13TreeNodeId
     *
     * @return SahsSc13SeqItem
     */
    public function setSahsSc13TreeNodeId($sahsSc13TreeNodeId)
    {
        $this->sahsSc13TreeNodeId = $sahsSc13TreeNodeId;

        return $this;
    }

    /**
     * Get sahsSc13TreeNodeId.
     *
     * @return int
     */
    public function getSahsSc13TreeNodeId()
    {
        return $this->sahsSc13TreeNodeId;
    }

    /**
     * Set rootlevel.
     *
     * @param bool $rootlevel
     *
     * @return SahsSc13SeqItem
     */
    public function setRootlevel($rootlevel)
    {
        $this->rootlevel = $rootlevel;

        return $this;
    }

    /**
     * Get rootlevel.
     *
     * @return bool
     */
    public function getRootlevel()
    {
        return $this->rootlevel;
    }

    /**
     * Set importid.
     *
     * @param string|null $importid
     *
     * @return SahsSc13SeqItem
     */
    public function setImportid($importid = null)
    {
        $this->importid = $importid;

        return $this;
    }

    /**
     * Get importid.
     *
     * @return string|null
     */
    public function getImportid()
    {
        return $this->importid;
    }

    /**
     * Set seqnodeid.
     *
     * @param int $seqnodeid
     *
     * @return SahsSc13SeqItem
     */
    public function setSeqnodeid($seqnodeid)
    {
        $this->seqnodeid = $seqnodeid;

        return $this;
    }

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
     * Set sequencingid.
     *
     * @param string|null $sequencingid
     *
     * @return SahsSc13SeqItem
     */
    public function setSequencingid($sequencingid = null)
    {
        $this->sequencingid = $sequencingid;

        return $this;
    }

    /**
     * Get sequencingid.
     *
     * @return string|null
     */
    public function getSequencingid()
    {
        return $this->sequencingid;
    }

    /**
     * Set nocopy.
     *
     * @param bool|null $nocopy
     *
     * @return SahsSc13SeqItem
     */
    public function setNocopy($nocopy = null)
    {
        $this->nocopy = $nocopy;

        return $this;
    }

    /**
     * Get nocopy.
     *
     * @return bool|null
     */
    public function getNocopy()
    {
        return $this->nocopy;
    }

    /**
     * Set nodelete.
     *
     * @param bool|null $nodelete
     *
     * @return SahsSc13SeqItem
     */
    public function setNodelete($nodelete = null)
    {
        $this->nodelete = $nodelete;

        return $this;
    }

    /**
     * Get nodelete.
     *
     * @return bool|null
     */
    public function getNodelete()
    {
        return $this->nodelete;
    }

    /**
     * Set nomove.
     *
     * @param bool|null $nomove
     *
     * @return SahsSc13SeqItem
     */
    public function setNomove($nomove = null)
    {
        $this->nomove = $nomove;

        return $this;
    }

    /**
     * Get nomove.
     *
     * @return bool|null
     */
    public function getNomove()
    {
        return $this->nomove;
    }

    /**
     * Set seqxml.
     *
     * @param string|null $seqxml
     *
     * @return SahsSc13SeqItem
     */
    public function setSeqxml($seqxml = null)
    {
        $this->seqxml = $seqxml;

        return $this;
    }

    /**
     * Get seqxml.
     *
     * @return string|null
     */
    public function getSeqxml()
    {
        return $this->seqxml;
    }

    /**
     * Set importseqxml.
     *
     * @param string|null $importseqxml
     *
     * @return SahsSc13SeqItem
     */
    public function setImportseqxml($importseqxml = null)
    {
        $this->importseqxml = $importseqxml;

        return $this;
    }

    /**
     * Get importseqxml.
     *
     * @return string|null
     */
    public function getImportseqxml()
    {
        return $this->importseqxml;
    }
}
