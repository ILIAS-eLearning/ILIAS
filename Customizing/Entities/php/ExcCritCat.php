<?php



/**
 * ExcCritCat
 */
class ExcCritCat
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $parent = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var int
     */
    private $pos = '0';


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
     * Set parent.
     *
     * @param int $parent
     *
     * @return ExcCritCat
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return ExcCritCat
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
     * Set pos.
     *
     * @param int $pos
     *
     * @return ExcCritCat
     */
    public function setPos($pos)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get pos.
     *
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }
}
