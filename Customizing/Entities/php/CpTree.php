<?php



/**
 * CpTree
 */
class CpTree
{
    /**
     * @var int
     */
    private $child = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int|null
     */
    private $depth;

    /**
     * @var int|null
     */
    private $lft;

    /**
     * @var int|null
     */
    private $parent;

    /**
     * @var int|null
     */
    private $rgt;


    /**
     * Set child.
     *
     * @param int $child
     *
     * @return CpTree
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
     * Set objId.
     *
     * @param int $objId
     *
     * @return CpTree
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set depth.
     *
     * @param int|null $depth
     *
     * @return CpTree
     */
    public function setDepth($depth = null)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Get depth.
     *
     * @return int|null
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Set lft.
     *
     * @param int|null $lft
     *
     * @return CpTree
     */
    public function setLft($lft = null)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft.
     *
     * @return int|null
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set parent.
     *
     * @param int|null $parent
     *
     * @return CpTree
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return int|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set rgt.
     *
     * @param int|null $rgt
     *
     * @return CpTree
     */
    public function setRgt($rgt = null)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt.
     *
     * @return int|null
     */
    public function getRgt()
    {
        return $this->rgt;
    }
}
