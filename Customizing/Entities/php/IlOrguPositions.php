<?php



/**
 * IlOrguPositions
 */
class IlOrguPositions
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var bool|null
     */
    private $corePosition;

    /**
     * @var bool|null
     */
    private $coreIdentifier;


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
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlOrguPositions
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
     * @return IlOrguPositions
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
     * Set corePosition.
     *
     * @param bool|null $corePosition
     *
     * @return IlOrguPositions
     */
    public function setCorePosition($corePosition = null)
    {
        $this->corePosition = $corePosition;

        return $this;
    }

    /**
     * Get corePosition.
     *
     * @return bool|null
     */
    public function getCorePosition()
    {
        return $this->corePosition;
    }

    /**
     * Set coreIdentifier.
     *
     * @param bool|null $coreIdentifier
     *
     * @return IlOrguPositions
     */
    public function setCoreIdentifier($coreIdentifier = null)
    {
        $this->coreIdentifier = $coreIdentifier;

        return $this;
    }

    /**
     * Get coreIdentifier.
     *
     * @return bool|null
     */
    public function getCoreIdentifier()
    {
        return $this->coreIdentifier;
    }
}
