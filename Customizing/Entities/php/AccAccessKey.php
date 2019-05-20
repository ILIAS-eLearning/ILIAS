<?php



/**
 * AccAccessKey
 */
class AccAccessKey
{
    /**
     * @var string
     */
    private $langKey = '';

    /**
     * @var int
     */
    private $functionId = '0';

    /**
     * @var string|null
     */
    private $accessKey;


    /**
     * Set langKey.
     *
     * @param string $langKey
     *
     * @return AccAccessKey
     */
    public function setLangKey($langKey)
    {
        $this->langKey = $langKey;

        return $this;
    }

    /**
     * Get langKey.
     *
     * @return string
     */
    public function getLangKey()
    {
        return $this->langKey;
    }

    /**
     * Set functionId.
     *
     * @param int $functionId
     *
     * @return AccAccessKey
     */
    public function setFunctionId($functionId)
    {
        $this->functionId = $functionId;

        return $this;
    }

    /**
     * Get functionId.
     *
     * @return int
     */
    public function getFunctionId()
    {
        return $this->functionId;
    }

    /**
     * Set accessKey.
     *
     * @param string|null $accessKey
     *
     * @return AccAccessKey
     */
    public function setAccessKey($accessKey = null)
    {
        $this->accessKey = $accessKey;

        return $this;
    }

    /**
     * Get accessKey.
     *
     * @return string|null
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }
}
