<?php



/**
 * CopgPobjDef
 */
class CopgPobjDef
{
    /**
     * @var string
     */
    private $parentType = '';

    /**
     * @var string
     */
    private $className = '';

    /**
     * @var string|null
     */
    private $directory;

    /**
     * @var string|null
     */
    private $component;


    /**
     * Get parentType.
     *
     * @return string
     */
    public function getParentType()
    {
        return $this->parentType;
    }

    /**
     * Set className.
     *
     * @param string $className
     *
     * @return CopgPobjDef
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get className.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set directory.
     *
     * @param string|null $directory
     *
     * @return CopgPobjDef
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
     * Set component.
     *
     * @param string|null $component
     *
     * @return CopgPobjDef
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
}
