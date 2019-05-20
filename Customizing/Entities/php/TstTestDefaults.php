<?php



/**
 * TstTestDefaults
 */
class TstTestDefaults
{
    /**
     * @var int
     */
    private $testDefaultsId = '0';

    /**
     * @var int
     */
    private $userFi = '0';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $marks;

    /**
     * @var string|null
     */
    private $defaults;


    /**
     * Get testDefaultsId.
     *
     * @return int
     */
    public function getTestDefaultsId()
    {
        return $this->testDefaultsId;
    }

    /**
     * Set userFi.
     *
     * @param int $userFi
     *
     * @return TstTestDefaults
     */
    public function setUserFi($userFi)
    {
        $this->userFi = $userFi;

        return $this;
    }

    /**
     * Get userFi.
     *
     * @return int
     */
    public function getUserFi()
    {
        return $this->userFi;
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return TstTestDefaults
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstTestDefaults
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set marks.
     *
     * @param string|null $marks
     *
     * @return TstTestDefaults
     */
    public function setMarks($marks = null)
    {
        $this->marks = $marks;

        return $this;
    }

    /**
     * Get marks.
     *
     * @return string|null
     */
    public function getMarks()
    {
        return $this->marks;
    }

    /**
     * Set defaults.
     *
     * @param string|null $defaults
     *
     * @return TstTestDefaults
     */
    public function setDefaults($defaults = null)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Get defaults.
     *
     * @return string|null
     */
    public function getDefaults()
    {
        return $this->defaults;
    }
}
