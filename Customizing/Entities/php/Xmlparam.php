<?php



/**
 * Xmlparam
 */
class Xmlparam
{
    /**
     * @var int
     */
    private $tagFk = '0';

    /**
     * @var string
     */
    private $paramName = '';

    /**
     * @var string|null
     */
    private $paramValue;


    /**
     * Set tagFk.
     *
     * @param int $tagFk
     *
     * @return Xmlparam
     */
    public function setTagFk($tagFk)
    {
        $this->tagFk = $tagFk;

        return $this;
    }

    /**
     * Get tagFk.
     *
     * @return int
     */
    public function getTagFk()
    {
        return $this->tagFk;
    }

    /**
     * Set paramName.
     *
     * @param string $paramName
     *
     * @return Xmlparam
     */
    public function setParamName($paramName)
    {
        $this->paramName = $paramName;

        return $this;
    }

    /**
     * Get paramName.
     *
     * @return string
     */
    public function getParamName()
    {
        return $this->paramName;
    }

    /**
     * Set paramValue.
     *
     * @param string|null $paramValue
     *
     * @return Xmlparam
     */
    public function setParamValue($paramValue = null)
    {
        $this->paramValue = $paramValue;

        return $this;
    }

    /**
     * Get paramValue.
     *
     * @return string|null
     */
    public function getParamValue()
    {
        return $this->paramValue;
    }
}
