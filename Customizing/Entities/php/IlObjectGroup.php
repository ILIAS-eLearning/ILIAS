<?php



/**
 * IlObjectGroup
 */
class IlObjectGroup
{
    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var int
     */
    private $defaultPresPos = '0';


    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return IlObjectGroup
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set defaultPresPos.
     *
     * @param int $defaultPresPos
     *
     * @return IlObjectGroup
     */
    public function setDefaultPresPos($defaultPresPos)
    {
        $this->defaultPresPos = $defaultPresPos;

        return $this;
    }

    /**
     * Get defaultPresPos.
     *
     * @return int
     */
    public function getDefaultPresPos()
    {
        return $this->defaultPresPos;
    }
}
