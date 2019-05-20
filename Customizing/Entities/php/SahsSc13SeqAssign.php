<?php



/**
 * SahsSc13SeqAssign
 */
class SahsSc13SeqAssign
{
    /**
     * @var int
     */
    private $sahsSc13TreeNodeId = '0';

    /**
     * @var string|null
     */
    private $identifier;


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
     * Set identifier.
     *
     * @param string|null $identifier
     *
     * @return SahsSc13SeqAssign
     */
    public function setIdentifier($identifier = null)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier.
     *
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
