<?php



/**
 * SahsSc13SeqTree
 */
class SahsSc13SeqTree
{
    /**
     * @var int
     */
    private $child = '0';

    /**
     * @var string
     */
    private $importid = '';

    /**
     * @var int
     */
    private $parent = '0';

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
    private $rgt;


    /**
     * Set child.
     *
     * @param int $child
     *
     * @return SahsSc13SeqTree
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
     * Set importid.
     *
     * @param string $importid
     *
     * @return SahsSc13SeqTree
     */
    public function setImportid($importid)
    {
        $this->importid = $importid;

        return $this;
    }

    /**
     * Get importid.
     *
     * @return string
     */
    public function getImportid()
    {
        return $this->importid;
    }

    /**
     * Set parent.
     *
     * @param int $parent
     *
     * @return SahsSc13SeqTree
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
     * Set depth.
     *
     * @param int|null $depth
     *
     * @return SahsSc13SeqTree
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
     * @return SahsSc13SeqTree
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
     * @return SahsSc13SeqTree
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
