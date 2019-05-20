<?php



/**
 * RegAccessLimit
 */
class RegAccessLimit
{
    /**
     * @var int
     */
    private $roleId = '0';

    /**
     * @var int|null
     */
    private $limitAbsolute;

    /**
     * @var int|null
     */
    private $limitRelativeD;

    /**
     * @var int|null
     */
    private $limitRelativeM;

    /**
     * @var int|null
     */
    private $limitRelativeY;

    /**
     * @var string|null
     */
    private $limitMode = 'absolute';


    /**
     * Get roleId.
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set limitAbsolute.
     *
     * @param int|null $limitAbsolute
     *
     * @return RegAccessLimit
     */
    public function setLimitAbsolute($limitAbsolute = null)
    {
        $this->limitAbsolute = $limitAbsolute;

        return $this;
    }

    /**
     * Get limitAbsolute.
     *
     * @return int|null
     */
    public function getLimitAbsolute()
    {
        return $this->limitAbsolute;
    }

    /**
     * Set limitRelativeD.
     *
     * @param int|null $limitRelativeD
     *
     * @return RegAccessLimit
     */
    public function setLimitRelativeD($limitRelativeD = null)
    {
        $this->limitRelativeD = $limitRelativeD;

        return $this;
    }

    /**
     * Get limitRelativeD.
     *
     * @return int|null
     */
    public function getLimitRelativeD()
    {
        return $this->limitRelativeD;
    }

    /**
     * Set limitRelativeM.
     *
     * @param int|null $limitRelativeM
     *
     * @return RegAccessLimit
     */
    public function setLimitRelativeM($limitRelativeM = null)
    {
        $this->limitRelativeM = $limitRelativeM;

        return $this;
    }

    /**
     * Get limitRelativeM.
     *
     * @return int|null
     */
    public function getLimitRelativeM()
    {
        return $this->limitRelativeM;
    }

    /**
     * Set limitRelativeY.
     *
     * @param int|null $limitRelativeY
     *
     * @return RegAccessLimit
     */
    public function setLimitRelativeY($limitRelativeY = null)
    {
        $this->limitRelativeY = $limitRelativeY;

        return $this;
    }

    /**
     * Get limitRelativeY.
     *
     * @return int|null
     */
    public function getLimitRelativeY()
    {
        return $this->limitRelativeY;
    }

    /**
     * Set limitMode.
     *
     * @param string|null $limitMode
     *
     * @return RegAccessLimit
     */
    public function setLimitMode($limitMode = null)
    {
        $this->limitMode = $limitMode;

        return $this;
    }

    /**
     * Get limitMode.
     *
     * @return string|null
     */
    public function getLimitMode()
    {
        return $this->limitMode;
    }
}
