<?php



/**
 * Xmlvalue
 */
class Xmlvalue
{
    /**
     * @var int
     */
    private $tagValuePk = '0';

    /**
     * @var int
     */
    private $tagFk = '0';

    /**
     * @var string|null
     */
    private $tagValue;


    /**
     * Get tagValuePk.
     *
     * @return int
     */
    public function getTagValuePk()
    {
        return $this->tagValuePk;
    }

    /**
     * Set tagFk.
     *
     * @param int $tagFk
     *
     * @return Xmlvalue
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
     * Set tagValue.
     *
     * @param string|null $tagValue
     *
     * @return Xmlvalue
     */
    public function setTagValue($tagValue = null)
    {
        $this->tagValue = $tagValue;

        return $this;
    }

    /**
     * Get tagValue.
     *
     * @return string|null
     */
    public function getTagValue()
    {
        return $this->tagValue;
    }
}
