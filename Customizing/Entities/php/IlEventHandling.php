<?php



/**
 * IlEventHandling
 */
class IlEventHandling
{
    /**
     * @var string
     */
    private $component = '';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string
     */
    private $id = '';


    /**
     * Set component.
     *
     * @param string $component
     *
     * @return IlEventHandling
     */
    public function setComponent($component)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get component.
     *
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return IlEventHandling
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
     * @return IlEventHandling
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
}
