<?php



/**
 * FrmPostsTree
 */
class FrmPostsTree
{
    /**
     * @var int
     */
    private $fptPk = '0';

    /**
     * @var int
     */
    private $thrFk = '0';

    /**
     * @var int
     */
    private $posFk = '0';

    /**
     * @var int
     */
    private $parentPos = '0';

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
     * @var \DateTime|null
     */
    private $fptDate;


    /**
     * Get fptPk.
     *
     * @return int
     */
    public function getFptPk()
    {
        return $this->fptPk;
    }

    /**
     * Set thrFk.
     *
     * @param int $thrFk
     *
     * @return FrmPostsTree
     */
    public function setThrFk($thrFk)
    {
        $this->thrFk = $thrFk;

        return $this;
    }

    /**
     * Get thrFk.
     *
     * @return int
     */
    public function getThrFk()
    {
        return $this->thrFk;
    }

    /**
     * Set posFk.
     *
     * @param int $posFk
     *
     * @return FrmPostsTree
     */
    public function setPosFk($posFk)
    {
        $this->posFk = $posFk;

        return $this;
    }

    /**
     * Get posFk.
     *
     * @return int
     */
    public function getPosFk()
    {
        return $this->posFk;
    }

    /**
     * Set parentPos.
     *
     * @param int $parentPos
     *
     * @return FrmPostsTree
     */
    public function setParentPos($parentPos)
    {
        $this->parentPos = $parentPos;

        return $this;
    }

    /**
     * Get parentPos.
     *
     * @return int
     */
    public function getParentPos()
    {
        return $this->parentPos;
    }

    /**
     * Set lft.
     *
     * @param int $lft
     *
     * @return FrmPostsTree
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
     * @return FrmPostsTree
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
     * @return FrmPostsTree
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

    /**
     * Set fptDate.
     *
     * @param \DateTime|null $fptDate
     *
     * @return FrmPostsTree
     */
    public function setFptDate($fptDate = null)
    {
        $this->fptDate = $fptDate;

        return $this;
    }

    /**
     * Get fptDate.
     *
     * @return \DateTime|null
     */
    public function getFptDate()
    {
        return $this->fptDate;
    }
}
