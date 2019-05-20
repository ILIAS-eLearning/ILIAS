<?php



/**
 * IlComponent
 */
class IlComponent
{
    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string|null
     */
    private $name;


    /**
     * Set type.
     *
     * @param string $type
     *
     * @return IlComponent
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set id.
     *
     * @param string $id
     *
     * @return IlComponent
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * @return IlComponent
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
}
