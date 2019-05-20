<?php



/**
 * DidacticTplFp
 */
class DidacticTplFp
{
    /**
     * @var int
     */
    private $patternId = '0';

    /**
     * @var bool
     */
    private $patternType = '0';

    /**
     * @var bool
     */
    private $patternSubType = '0';

    /**
     * @var string|null
     */
    private $pattern;

    /**
     * @var int
     */
    private $parentId = '0';

    /**
     * @var string|null
     */
    private $parentType;


    /**
     * Get patternId.
     *
     * @return int
     */
    public function getPatternId()
    {
        return $this->patternId;
    }

    /**
     * Set patternType.
     *
     * @param bool $patternType
     *
     * @return DidacticTplFp
     */
    public function setPatternType($patternType)
    {
        $this->patternType = $patternType;

        return $this;
    }

    /**
     * Get patternType.
     *
     * @return bool
     */
    public function getPatternType()
    {
        return $this->patternType;
    }

    /**
     * Set patternSubType.
     *
     * @param bool $patternSubType
     *
     * @return DidacticTplFp
     */
    public function setPatternSubType($patternSubType)
    {
        $this->patternSubType = $patternSubType;

        return $this;
    }

    /**
     * Get patternSubType.
     *
     * @return bool
     */
    public function getPatternSubType()
    {
        return $this->patternSubType;
    }

    /**
     * Set pattern.
     *
     * @param string|null $pattern
     *
     * @return DidacticTplFp
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

    /**
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return DidacticTplFp
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set parentType.
     *
     * @param string|null $parentType
     *
     * @return DidacticTplFp
     */
    public function setParentType($parentType = null)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parentType.
     *
     * @return string|null
     */
    public function getParentType()
    {
        return $this->parentType;
    }
}
