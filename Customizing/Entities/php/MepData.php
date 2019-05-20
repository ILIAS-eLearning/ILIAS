<?php



/**
 * MepData
 */
class MepData
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $defaultWidth;

    /**
     * @var int|null
     */
    private $defaultHeight;

    /**
     * @var bool
     */
    private $forTranslation = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set defaultWidth.
     *
     * @param int|null $defaultWidth
     *
     * @return MepData
     */
    public function setDefaultWidth($defaultWidth = null)
    {
        $this->defaultWidth = $defaultWidth;

        return $this;
    }

    /**
     * Get defaultWidth.
     *
     * @return int|null
     */
    public function getDefaultWidth()
    {
        return $this->defaultWidth;
    }

    /**
     * Set defaultHeight.
     *
     * @param int|null $defaultHeight
     *
     * @return MepData
     */
    public function setDefaultHeight($defaultHeight = null)
    {
        $this->defaultHeight = $defaultHeight;

        return $this;
    }

    /**
     * Get defaultHeight.
     *
     * @return int|null
     */
    public function getDefaultHeight()
    {
        return $this->defaultHeight;
    }

    /**
     * Set forTranslation.
     *
     * @param bool $forTranslation
     *
     * @return MepData
     */
    public function setForTranslation($forTranslation)
    {
        $this->forTranslation = $forTranslation;

        return $this;
    }

    /**
     * Get forTranslation.
     *
     * @return bool
     */
    public function getForTranslation()
    {
        return $this->forTranslation;
    }
}
