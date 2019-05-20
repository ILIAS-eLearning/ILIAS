<?php



/**
 * SklTree
 */
class SklTree
{
    /**
     * @var int
     */
    private $sklTreeId = '0';

    /**
     * @var int
     */
    private $child = '0';

    /**
     * @var int|null
     */
    private $parent;

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
     * Set sklTreeId.
     *
     * @param int $sklTreeId
     *
     * @return SklTree
     */
    public function setSklTreeId($sklTreeId)
    {
        $this->sklTreeId = $sklTreeId;

        return $this;
    }

    /**
     * Get sklTreeId.
     *
     * @return int
     */
    public function getSklTreeId()
    {
        return $this->sklTreeId;
    }

    /**
     * Set child.
     *
     * @param int $child
     *
     * @return SklTree
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
     * @param int|null $parent
     *
     * @return SklTree
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
     * Set lft.
     *
     * @param int $lft
     *
     * @return SklTree
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
     * @return SklTree
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
     * @return SklTree
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
