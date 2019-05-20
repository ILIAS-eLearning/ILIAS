<?php



/**
 * SahsSc13SeqNode
 */
class SahsSc13SeqNode
{
    /**
     * @var int
     */
    private $seqnodeid = '0';

    /**
     * @var string|null
     */
    private $nodename;

    /**
     * @var int|null
     */
    private $treeNodeId;


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
     * Set nodename.
     *
     * @param string|null $nodename
     *
     * @return SahsSc13SeqNode
     */
    public function setNodename($nodename = null)
    {
        $this->nodename = $nodename;

        return $this;
    }

    /**
     * Get nodename.
     *
     * @return string|null
     */
    public function getNodename()
    {
        return $this->nodename;
    }

    /**
     * Set treeNodeId.
     *
     * @param int|null $treeNodeId
     *
     * @return SahsSc13SeqNode
     */
    public function setTreeNodeId($treeNodeId = null)
    {
        $this->treeNodeId = $treeNodeId;

        return $this;
    }

    /**
     * Get treeNodeId.
     *
     * @return int|null
     */
    public function getTreeNodeId()
    {
        return $this->treeNodeId;
    }
}
