<?php



/**
 * IntLink
 */
class IntLink
{
    /**
     * @var string
     */
    private $sourceType = ' ';

    /**
     * @var int
     */
    private $sourceId = '0';

    /**
     * @var string
     */
    private $targetType = ' ';

    /**
     * @var int
     */
    private $targetId = '0';

    /**
     * @var int
     */
    private $targetInst = '0';

    /**
     * @var string
     */
    private $sourceLang = '-';


    /**
     * Set sourceType.
     *
     * @param string $sourceType
     *
     * @return IntLink
     */
    public function setSourceType($sourceType)
    {
        $this->sourceType = $sourceType;

        return $this;
    }

    /**
     * Get sourceType.
     *
     * @return string
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * Set sourceId.
     *
     * @param int $sourceId
     *
     * @return IntLink
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    /**
     * Get sourceId.
     *
     * @return int
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Set targetType.
     *
     * @param string $targetType
     *
     * @return IntLink
     */
    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;

        return $this;
    }

    /**
     * Get targetType.
     *
     * @return string
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * Set targetId.
     *
     * @param int $targetId
     *
     * @return IntLink
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;

        return $this;
    }

    /**
     * Get targetId.
     *
     * @return int
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * Set targetInst.
     *
     * @param int $targetInst
     *
     * @return IntLink
     */
    public function setTargetInst($targetInst)
    {
        $this->targetInst = $targetInst;

        return $this;
    }

    /**
     * Get targetInst.
     *
     * @return int
     */
    public function getTargetInst()
    {
        return $this->targetInst;
    }

    /**
     * Set sourceLang.
     *
     * @param string $sourceLang
     *
     * @return IntLink
     */
    public function setSourceLang($sourceLang)
    {
        $this->sourceLang = $sourceLang;

        return $this;
    }

    /**
     * Get sourceLang.
     *
     * @return string
     */
    public function getSourceLang()
    {
        return $this->sourceLang;
    }
}
