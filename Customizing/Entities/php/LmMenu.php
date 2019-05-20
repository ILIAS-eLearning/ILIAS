<?php



/**
 * LmMenu
 */
class LmMenu
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $lmId = '0';

    /**
     * @var string|null
     */
    private $linkType = 'extern';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $target;

    /**
     * @var int|null
     */
    private $linkRefId;

    /**
     * @var string|null
     */
    private $active = 'n';


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
     * Set lmId.
     *
     * @param int $lmId
     *
     * @return LmMenu
     */
    public function setLmId($lmId)
    {
        $this->lmId = $lmId;

        return $this;
    }

    /**
     * Get lmId.
     *
     * @return int
     */
    public function getLmId()
    {
        return $this->lmId;
    }

    /**
     * Set linkType.
     *
     * @param string|null $linkType
     *
     * @return LmMenu
     */
    public function setLinkType($linkType = null)
    {
        $this->linkType = $linkType;

        return $this;
    }

    /**
     * Get linkType.
     *
     * @return string|null
     */
    public function getLinkType()
    {
        return $this->linkType;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return LmMenu
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
     * Set target.
     *
     * @param string|null $target
     *
     * @return LmMenu
     */
    public function setTarget($target = null)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target.
     *
     * @return string|null
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set linkRefId.
     *
     * @param int|null $linkRefId
     *
     * @return LmMenu
     */
    public function setLinkRefId($linkRefId = null)
    {
        $this->linkRefId = $linkRefId;

        return $this;
    }

    /**
     * Get linkRefId.
     *
     * @return int|null
     */
    public function getLinkRefId()
    {
        return $this->linkRefId;
    }

    /**
     * Set active.
     *
     * @param string|null $active
     *
     * @return LmMenu
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return string|null
     */
    public function getActive()
    {
        return $this->active;
    }
}
