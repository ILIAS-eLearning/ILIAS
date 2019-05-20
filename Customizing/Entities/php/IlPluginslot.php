<?php



/**
 * IlPluginslot
 */
class IlPluginslot
{
    /**
     * @var string
     */
    private $component = ' ';

    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string|null
     */
    private $name;


    /**
     * Set component.
     *
     * @param string $component
     *
     * @return IlPluginslot
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
     * Set id.
     *
     * @param string $id
     *
     * @return IlPluginslot
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
     * @return IlPluginslot
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
