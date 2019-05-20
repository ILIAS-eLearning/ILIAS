<?php



/**
 * PageEditorSettings
 */
class PageEditorSettings
{
    /**
     * @var string
     */
    private $settingsGrp = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set settingsGrp.
     *
     * @param string $settingsGrp
     *
     * @return PageEditorSettings
     */
    public function setSettingsGrp($settingsGrp)
    {
        $this->settingsGrp = $settingsGrp;

        return $this;
    }

    /**
     * Get settingsGrp.
     *
     * @return string
     */
    public function getSettingsGrp()
    {
        return $this->settingsGrp;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return PageEditorSettings
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return PageEditorSettings
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
