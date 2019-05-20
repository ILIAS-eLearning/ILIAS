<?php



/**
 * MapArea
 */
class MapArea
{
    /**
     * @var int
     */
    private $itemId = '0';

    /**
     * @var int
     */
    private $nr = '0';

    /**
     * @var string|null
     */
    private $shape;

    /**
     * @var string|null
     */
    private $coords;

    /**
     * @var string|null
     */
    private $linkType;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $href;

    /**
     * @var string|null
     */
    private $target;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $targetFrame;

    /**
     * @var string|null
     */
    private $highlightMode;

    /**
     * @var string|null
     */
    private $highlightClass;


    /**
     * Set itemId.
     *
     * @param int $itemId
     *
     * @return MapArea
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * Get itemId.
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set nr.
     *
     * @param int $nr
     *
     * @return MapArea
     */
    public function setNr($nr)
    {
        $this->nr = $nr;

        return $this;
    }

    /**
     * Get nr.
     *
     * @return int
     */
    public function getNr()
    {
        return $this->nr;
    }

    /**
     * Set shape.
     *
     * @param string|null $shape
     *
     * @return MapArea
     */
    public function setShape($shape = null)
    {
        $this->shape = $shape;

        return $this;
    }

    /**
     * Get shape.
     *
     * @return string|null
     */
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * Set coords.
     *
     * @param string|null $coords
     *
     * @return MapArea
     */
    public function setCoords($coords = null)
    {
        $this->coords = $coords;

        return $this;
    }

    /**
     * Get coords.
     *
     * @return string|null
     */
    public function getCoords()
    {
        return $this->coords;
    }

    /**
     * Set linkType.
     *
     * @param string|null $linkType
     *
     * @return MapArea
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
     * @return MapArea
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
     * Set href.
     *
     * @param string|null $href
     *
     * @return MapArea
     */
    public function setHref($href = null)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Get href.
     *
     * @return string|null
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Set target.
     *
     * @param string|null $target
     *
     * @return MapArea
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
     * Set type.
     *
     * @param string|null $type
     *
     * @return MapArea
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set targetFrame.
     *
     * @param string|null $targetFrame
     *
     * @return MapArea
     */
    public function setTargetFrame($targetFrame = null)
    {
        $this->targetFrame = $targetFrame;

        return $this;
    }

    /**
     * Get targetFrame.
     *
     * @return string|null
     */
    public function getTargetFrame()
    {
        return $this->targetFrame;
    }

    /**
     * Set highlightMode.
     *
     * @param string|null $highlightMode
     *
     * @return MapArea
     */
    public function setHighlightMode($highlightMode = null)
    {
        $this->highlightMode = $highlightMode;

        return $this;
    }

    /**
     * Get highlightMode.
     *
     * @return string|null
     */
    public function getHighlightMode()
    {
        return $this->highlightMode;
    }

    /**
     * Set highlightClass.
     *
     * @param string|null $highlightClass
     *
     * @return MapArea
     */
    public function setHighlightClass($highlightClass = null)
    {
        $this->highlightClass = $highlightClass;

        return $this;
    }

    /**
     * Get highlightClass.
     *
     * @return string|null
     */
    public function getHighlightClass()
    {
        return $this->highlightClass;
    }
}
