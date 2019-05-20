<?php



/**
 * MepTree
 */
class MepTree
{
    /**
     * @var int
     */
    private $mepId = '0';

    /**
     * @var int
     */
    private $child = '0';

    /**
     * @var int
     */
    private $parent = '0';

    /**
     * @var int
     */
    private $lft = '0';

    /**
     * @var int
     */
    private $rgt = '0';

    /**
     * @var int
     */
    private $depth = '0';


    /**
     * Set mepId.
     *
     * @param int $mepId
     *
     * @return MepTree
     */
    public function setMepId($mepId)
    {
        $this->mepId = $mepId;

        return $this;
    }

    /**
     * Get mepId.
     *
     * @return int
     */
    public function getMepId()
    {
        return $this->mepId;
    }

    /**
     * Set child.
     *
     * @param int $child
     *
     * @return MepTree
     */
    public function setChild($child)
    {
        $this->child = $child;

        return $this;
    }

    /**
     * Get child.
     *
     * @return int
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Set parent.
     *
     * @param int $parent
     *
     * @return MepTree
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set lft.
     *
     * @param int $lft
     *
     * @return MepTree
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft.
     *
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt.
     *
     * @param int $rgt
     *
     * @return MepTree
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt.
     *
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set depth.
     *
     * @param int $depth
     *
     * @return MepTree
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Get depth.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }
}
