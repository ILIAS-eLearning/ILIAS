<?php



/**
 * EcsCmsTree
 */
class EcsCmsTree
{
    /**
     * @var int
     */
    private $tree = '0';

    /**
     * @var int
     */
    private $child = '0';

    /**
     * @var int|null
     */
    private $parent;

    /**
     * @var int|null
     */
    private $lft;

    /**
     * @var int|null
     */
    private $rgt;

    /**
     * @var int|null
     */
    private $depth;


    /**
     * Set tree.
     *
     * @param int $tree
     *
     * @return EcsCmsTree
     */
    public function setTree($tree)
    {
        $this->tree = $tree;

        return $this;
    }

    /**
     * Get tree.
     *
     * @return int
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * Set child.
     *
     * @param int $child
     *
     * @return EcsCmsTree
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
     * @return EcsCmsTree
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
     * @param int|null $lft
     *
     * @return EcsCmsTree
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
     * Set rgt.
     *
     * @param int|null $rgt
     *
     * @return EcsCmsTree
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

    /**
     * Set depth.
     *
     * @param int|null $depth
     *
     * @return EcsCmsTree
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
}
