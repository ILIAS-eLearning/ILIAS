<?php



/**
 * Xmlnestedsettmp
 */
class Xmlnestedsettmp
{
    /**
     * @var int
     */
    private $nsId = '0';

    /**
     * @var string
     */
    private $nsUniqueId = '';

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
     * Set nsUniqueId.
     *
     * @param string $nsUniqueId
     *
     * @return Xmlnestedsettmp
     */
    public function setNsUniqueId($nsUniqueId)
    {
        $this->nsUniqueId = $nsUniqueId;

        return $this;
    }

    /**
     * Get nsUniqueId.
     *
     * @return string
     */
    public function getNsUniqueId()
    {
        return $this->nsUniqueId;
    }

    /**
     * Set nsBookFk.
     *
     * @param int $nsBookFk
     *
     * @return Xmlnestedsettmp
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
     * @return Xmlnestedsettmp
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
     * @return Xmlnestedsettmp
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
     * @return Xmlnestedsettmp
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
     * @return Xmlnestedsettmp
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
