<?php



/**
 * CrsItems
 */
class CrsItems
{
    /**
     * @var int
     */
    private $parentId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var bool|null
     */
    private $timingType;

    /**
     * @var int
     */
    private $timingStart = '0';

    /**
     * @var int
     */
    private $timingEnd = '0';

    /**
     * @var int
     */
    private $suggestionStart = '0';

    /**
     * @var int
     */
    private $suggestionEnd = '0';

    /**
     * @var bool
     */
    private $changeable = '0';

    /**
     * @var bool
     */
    private $visible = '0';

    /**
     * @var int|null
     */
    private $position;

    /**
     * @var int|null
     */
    private $suggestionStartRel = '0';

    /**
     * @var int|null
     */
    private $suggestionEndRel = '0';


    /**
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return CrsItems
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
     * Set objId.
     *
     * @param int $objId
     *
     * @return CrsItems
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set timingType.
     *
     * @param bool|null $timingType
     *
     * @return CrsItems
     */
    public function setTimingType($timingType = null)
    {
        $this->timingType = $timingType;

        return $this;
    }

    /**
     * Get timingType.
     *
     * @return bool|null
     */
    public function getTimingType()
    {
        return $this->timingType;
    }

    /**
     * Set timingStart.
     *
     * @param int $timingStart
     *
     * @return CrsItems
     */
    public function setTimingStart($timingStart)
    {
        $this->timingStart = $timingStart;

        return $this;
    }

    /**
     * Get timingStart.
     *
     * @return int
     */
    public function getTimingStart()
    {
        return $this->timingStart;
    }

    /**
     * Set timingEnd.
     *
     * @param int $timingEnd
     *
     * @return CrsItems
     */
    public function setTimingEnd($timingEnd)
    {
        $this->timingEnd = $timingEnd;

        return $this;
    }

    /**
     * Get timingEnd.
     *
     * @return int
     */
    public function getTimingEnd()
    {
        return $this->timingEnd;
    }

    /**
     * Set suggestionStart.
     *
     * @param int $suggestionStart
     *
     * @return CrsItems
     */
    public function setSuggestionStart($suggestionStart)
    {
        $this->suggestionStart = $suggestionStart;

        return $this;
    }

    /**
     * Get suggestionStart.
     *
     * @return int
     */
    public function getSuggestionStart()
    {
        return $this->suggestionStart;
    }

    /**
     * Set suggestionEnd.
     *
     * @param int $suggestionEnd
     *
     * @return CrsItems
     */
    public function setSuggestionEnd($suggestionEnd)
    {
        $this->suggestionEnd = $suggestionEnd;

        return $this;
    }

    /**
     * Get suggestionEnd.
     *
     * @return int
     */
    public function getSuggestionEnd()
    {
        return $this->suggestionEnd;
    }

    /**
     * Set changeable.
     *
     * @param bool $changeable
     *
     * @return CrsItems
     */
    public function setChangeable($changeable)
    {
        $this->changeable = $changeable;

        return $this;
    }

    /**
     * Get changeable.
     *
     * @return bool
     */
    public function getChangeable()
    {
        return $this->changeable;
    }

    /**
     * Set visible.
     *
     * @param bool $visible
     *
     * @return CrsItems
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set position.
     *
     * @param int|null $position
     *
     * @return CrsItems
     */
    public function setPosition($position = null)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set suggestionStartRel.
     *
     * @param int|null $suggestionStartRel
     *
     * @return CrsItems
     */
    public function setSuggestionStartRel($suggestionStartRel = null)
    {
        $this->suggestionStartRel = $suggestionStartRel;

        return $this;
    }

    /**
     * Get suggestionStartRel.
     *
     * @return int|null
     */
    public function getSuggestionStartRel()
    {
        return $this->suggestionStartRel;
    }

    /**
     * Set suggestionEndRel.
     *
     * @param int|null $suggestionEndRel
     *
     * @return CrsItems
     */
    public function setSuggestionEndRel($suggestionEndRel = null)
    {
        $this->suggestionEndRel = $suggestionEndRel;

        return $this;
    }

    /**
     * Get suggestionEndRel.
     *
     * @return int|null
     */
    public function getSuggestionEndRel()
    {
        return $this->suggestionEndRel;
    }
}
