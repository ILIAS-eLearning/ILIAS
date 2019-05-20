<?php



/**
 * CmiCorrectResponse
 */
class CmiCorrectResponse
{
    /**
     * @var int
     */
    private $cmiCorrectRespId = '0';

    /**
     * @var int|null
     */
    private $cmiInteractionId;

    /**
     * @var string|null
     */
    private $pattern;


    /**
     * Get cmiCorrectRespId.
     *
     * @return int
     */
    public function getCmiCorrectRespId()
    {
        return $this->cmiCorrectRespId;
    }

    /**
     * Set cmiInteractionId.
     *
     * @param int|null $cmiInteractionId
     *
     * @return CmiCorrectResponse
     */
    public function setCmiInteractionId($cmiInteractionId = null)
    {
        $this->cmiInteractionId = $cmiInteractionId;

        return $this;
    }

    /**
     * Get cmiInteractionId.
     *
     * @return int|null
     */
    public function getCmiInteractionId()
    {
        return $this->cmiInteractionId;
    }

    /**
     * Set pattern.
     *
     * @param string|null $pattern
     *
     * @return CmiCorrectResponse
     */
    public function setPattern($pattern = null)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get pattern.
     *
     * @return string|null
     */
    public function getPattern()
    {
        return $this->pattern;
    }
}
