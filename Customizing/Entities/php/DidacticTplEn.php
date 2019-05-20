<?php



/**
 * DidacticTplEn
 */
class DidacticTplEn
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $node = '0';


    /**
     * Set id.
     *
     * @param int $id
     *
     * @return DidacticTplEn
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set node.
     *
     * @param int $node
     *
     * @return DidacticTplEn
     */
    public function setNode($node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node.
     *
     * @return int
     */
    public function getNode()
    {
        return $this->node;
    }
}
