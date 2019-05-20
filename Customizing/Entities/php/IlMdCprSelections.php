<?php



/**
 * IlMdCprSelections
 */
class IlMdCprSelections
{
    /**
     * @var int
     */
    private $entryId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $copyright;

    /**
     * @var string|null
     */
    private $language;

    /**
     * @var bool
     */
    private $costs = '0';

    /**
     * @var bool
     */
    private $cprRestrictions = '1';

    /**
     * @var bool
     */
    private $isDefault = '0';

    /**
     * @var bool
     */
    private $outdated = '0';

    /**
     * @var bool
     */
    private $position = '0';


    /**
     * Get entryId.
     *
     * @return int
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlMdCprSelections
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return IlMdCprSelections
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set copyright.
     *
     * @param string|null $copyright
     *
     * @return IlMdCprSelections
     */
    public function setCopyright($copyright = null)
    {
        $this->copyright = $copyright;

        return $this;
    }

    /**
     * Get copyright.
     *
     * @return string|null
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * Set language.
     *
     * @param string|null $language
     *
     * @return IlMdCprSelections
     */
    public function setLanguage($language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set costs.
     *
     * @param bool $costs
     *
     * @return IlMdCprSelections
     */
    public function setCosts($costs)
    {
        $this->costs = $costs;

        return $this;
    }

    /**
     * Get costs.
     *
     * @return bool
     */
    public function getCosts()
    {
        return $this->costs;
    }

    /**
     * Set cprRestrictions.
     *
     * @param bool $cprRestrictions
     *
     * @return IlMdCprSelections
     */
    public function setCprRestrictions($cprRestrictions)
    {
        $this->cprRestrictions = $cprRestrictions;

        return $this;
    }

    /**
     * Get cprRestrictions.
     *
     * @return bool
     */
    public function getCprRestrictions()
    {
        return $this->cprRestrictions;
    }

    /**
     * Set isDefault.
     *
     * @param bool $isDefault
     *
     * @return IlMdCprSelections
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault.
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set outdated.
     *
     * @param bool $outdated
     *
     * @return IlMdCprSelections
     */
    public function setOutdated($outdated)
    {
        $this->outdated = $outdated;

        return $this;
    }

    /**
     * Get outdated.
     *
     * @return bool
     */
    public function getOutdated()
    {
        return $this->outdated;
    }

    /**
     * Set position.
     *
     * @param bool $position
     *
     * @return IlMdCprSelections
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return bool
     */
    public function getPosition()
    {
        return $this->position;
    }
}
