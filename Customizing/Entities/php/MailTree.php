<?php



/**
 * MailTree
 */
class MailTree
{
    /**
     * @var int
     */
    private $child = '0';

    /**
     * @var int
     */
    private $tree = '0';

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
     * Get child.
     *
     * @return int
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Set tree.
     *
     * @param int $tree
     *
     * @return MailTree
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
     * Set parent.
     *
     * @param int|null $parent
     *
     * @return MailTree
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
     * @return MailTree
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
     * @return MailTree
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
     * @return MailTree
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
