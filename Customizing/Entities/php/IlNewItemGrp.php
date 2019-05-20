<?php



/**
 * IlNewItemGrp
 */
class IlNewItemGrp
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $titles;

    /**
     * @var int
     */
    private $pos = '0';

    /**
     * @var bool
     */
    private $type = '1';


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
     * Set titles.
     *
     * @param string|null $titles
     *
     * @return IlNewItemGrp
     */
    public function setTitles($titles = null)
    {
        $this->titles = $titles;

        return $this;
    }

    /**
     * Get titles.
     *
     * @return string|null
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * Set pos.
     *
     * @param int $pos
     *
     * @return IlNewItemGrp
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
     * Set type.
     *
     * @param bool $type
     *
     * @return IlNewItemGrp
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }
}
