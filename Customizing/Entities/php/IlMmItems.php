<?php



/**
 * IlMmItems
 */
class IlMmItems
{
    /**
     * @var string
     */
    private $identification = '';

    /**
     * @var bool|null
     */
    private $active;

    /**
     * @var int|null
     */
    private $position;

    /**
     * @var string|null
     */
    private $parentIdentification;


    /**
     * Get identification.
     *
     * @return string
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * Set active.
     *
     * @param bool|null $active
     *
     * @return IlMmItems
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set position.
     *
     * @param int|null $position
     *
     * @return IlMmItems
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
     * Set parentIdentification.
     *
     * @param string|null $parentIdentification
     *
     * @return IlMmItems
     */
    public function setParentIdentification($parentIdentification = null)
    {
        $this->parentIdentification = $parentIdentification;

        return $this;
    }

    /**
     * Get parentIdentification.
     *
     * @return string|null
     */
    public function getParentIdentification()
    {
        return $this->parentIdentification;
    }
}
