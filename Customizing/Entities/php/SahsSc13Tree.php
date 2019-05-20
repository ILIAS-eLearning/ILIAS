<?php



/**
 * SahsSc13Tree
 */
class SahsSc13Tree
{
    /**
     * @var int
     */
    private $slmId = '0';

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
     * Set slmId.
     *
     * @param int $slmId
     *
     * @return SahsSc13Tree
     */
    public function setSlmId($slmId)
    {
        $this->slmId = $slmId;

        return $this;
    }

    /**
     * Get slmId.
     *
     * @return int
     */
    public function getSlmId()
    {
        return $this->slmId;
    }

    /**
     * Set child.
     *
     * @param int $child
     *
     * @return SahsSc13Tree
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
     * @return SahsSc13Tree
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
     * @return SahsSc13Tree
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
     * @return SahsSc13Tree
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
     * @return SahsSc13Tree
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
