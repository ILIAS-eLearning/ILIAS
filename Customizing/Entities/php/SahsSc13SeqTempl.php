<?php



/**
 * SahsSc13SeqTempl
 */
class SahsSc13SeqTempl
{
    /**
     * @var int
     */
    private $seqnodeid = '0';

    /**
     * @var string
     */
    private $id = '';


    /**
     * Set seqnodeid.
     *
     * @param int $seqnodeid
     *
     * @return SahsSc13SeqTempl
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
     * Set id.
     *
     * @param string $id
     *
     * @return SahsSc13SeqTempl
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
