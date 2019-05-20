<?php



/**
 * CopgPcDef
 */
class CopgPcDef
{
    /**
     * @var string
     */
    private $pcType = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string|null
     */
    private $directory;

    /**
     * @var bool
     */
    private $intLinks = '0';

    /**
     * @var bool
     */
    private $styleClasses = '0';

    /**
     * @var bool
     */
    private $xsl = '0';

    /**
     * @var string|null
     */
    private $component;

    /**
     * @var bool|null
     */
    private $defEnabled = '0';


    /**
     * Get pcType.
     *
     * @return string
     */
    public function getPcType()
    {
        return $this->pcType;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return CopgPcDef
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
     * Set directory.
     *
     * @param string|null $directory
     *
     * @return CopgPcDef
     */
    public function setDirectory($directory = null)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory.
     *
     * @return string|null
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Set intLinks.
     *
     * @param bool $intLinks
     *
     * @return CopgPcDef
     */
    public function setIntLinks($intLinks)
    {
        $this->intLinks = $intLinks;

        return $this;
    }

    /**
     * Get intLinks.
     *
     * @return bool
     */
    public function getIntLinks()
    {
        return $this->intLinks;
    }

    /**
     * Set styleClasses.
     *
     * @param bool $styleClasses
     *
     * @return CopgPcDef
     */
    public function setStyleClasses($styleClasses)
    {
        $this->styleClasses = $styleClasses;

        return $this;
    }

    /**
     * Get styleClasses.
     *
     * @return bool
     */
    public function getStyleClasses()
    {
        return $this->styleClasses;
    }

    /**
     * Set xsl.
     *
     * @param bool $xsl
     *
     * @return CopgPcDef
     */
    public function setXsl($xsl)
    {
        $this->xsl = $xsl;

        return $this;
    }

    /**
     * Get xsl.
     *
     * @return bool
     */
    public function getXsl()
    {
        return $this->xsl;
    }

    /**
     * Set component.
     *
     * @param string|null $component
     *
     * @return CopgPcDef
     */
    public function setComponent($component = null)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get component.
     *
     * @return string|null
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set defEnabled.
     *
     * @param bool|null $defEnabled
     *
     * @return CopgPcDef
     */
    public function setDefEnabled($defEnabled = null)
    {
        $this->defEnabled = $defEnabled;

        return $this;
    }

    /**
     * Get defEnabled.
     *
     * @return bool|null
     */
    public function getDefEnabled()
    {
        return $this->defEnabled;
    }
}
