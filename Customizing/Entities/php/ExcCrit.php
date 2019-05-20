<?php



/**
 * ExcCrit
 */
class ExcCrit
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
    private $type;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $descr;

    /**
     * @var int
     */
    private $pos = '0';

    /**
     * @var bool
     */
    private $required = '0';

    /**
     * @var string|null
     */
    private $def;


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
     * @return ExcCrit
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
     * Set type.
     *
     * @param string|null $type
     *
     * @return ExcCrit
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
     * Set title.
     *
     * @param string|null $title
     *
     * @return ExcCrit
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
     * Set descr.
     *
     * @param string|null $descr
     *
     * @return ExcCrit
     */
    public function setDescr($descr = null)
    {
        $this->descr = $descr;

        return $this;
    }

    /**
     * Get descr.
     *
     * @return string|null
     */
    public function getDescr()
    {
        return $this->descr;
    }

    /**
     * Set pos.
     *
     * @param int $pos
     *
     * @return ExcCrit
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

    /**
     * Set required.
     *
     * @param bool $required
     *
     * @return ExcCrit
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required.
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set def.
     *
     * @param string|null $def
     *
     * @return ExcCrit
     */
    public function setDef($def = null)
    {
        $this->def = $def;

        return $this;
    }

    /**
     * Get def.
     *
     * @return string|null
     */
    public function getDef()
    {
        return $this->def;
    }
}
