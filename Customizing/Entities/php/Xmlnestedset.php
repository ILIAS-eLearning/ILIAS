<?php



/**
 * Xmlnestedset
 */
class Xmlnestedset
{
    /**
     * @var int
     */
    private $nsId = '0';

    /**
     * @var int
     */
    private $nsBookFk = '0';

    /**
     * @var string
     */
    private $nsType = '';

    /**
     * @var int
     */
    private $nsTagFk = '0';

    /**
     * @var int
     */
    private $nsL = '0';

    /**
     * @var int
     */
    private $nsR = '0';


    /**
     * Get nsId.
     *
     * @return int
     */
    public function getNsId()
    {
        return $this->nsId;
    }

    /**
     * Set nsBookFk.
     *
     * @param int $nsBookFk
     *
     * @return Xmlnestedset
     */
    public function setNsBookFk($nsBookFk)
    {
        $this->nsBookFk = $nsBookFk;

        return $this;
    }

    /**
     * Get nsBookFk.
     *
     * @return int
     */
    public function getNsBookFk()
    {
        return $this->nsBookFk;
    }

    /**
     * Set nsType.
     *
     * @param string $nsType
     *
     * @return Xmlnestedset
     */
    public function setNsType($nsType)
    {
        $this->nsType = $nsType;

        return $this;
    }

    /**
     * Get nsType.
     *
     * @return string
     */
    public function getNsType()
    {
        return $this->nsType;
    }

    /**
     * Set nsTagFk.
     *
     * @param int $nsTagFk
     *
     * @return Xmlnestedset
     */
    public function setNsTagFk($nsTagFk)
    {
        $this->nsTagFk = $nsTagFk;

        return $this;
    }

    /**
     * Get nsTagFk.
     *
     * @return int
     */
    public function getNsTagFk()
    {
        return $this->nsTagFk;
    }

    /**
     * Set nsL.
     *
     * @param int $nsL
     *
     * @return Xmlnestedset
     */
    public function setNsL($nsL)
    {
        $this->nsL = $nsL;

        return $this;
    }

    /**
     * Get nsL.
     *
     * @return int
     */
    public function getNsL()
    {
        return $this->nsL;
    }

    /**
     * Set nsR.
     *
     * @param int $nsR
     *
     * @return Xmlnestedset
     */
    public function setNsR($nsR)
    {
        $this->nsR = $nsR;

        return $this;
    }

    /**
     * Get nsR.
     *
     * @return int
     */
    public function getNsR()
    {
        return $this->nsR;
    }
}
