<?php



/**
 * UsrPref
 */
class UsrPref
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string
     */
    private $keyword = '';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return UsrPref
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set keyword.
     *
     * @param string $keyword
     *
     * @return UsrPref
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword.
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return UsrPref
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
