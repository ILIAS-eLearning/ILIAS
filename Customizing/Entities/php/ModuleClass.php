<?php



/**
 * ModuleClass
 */
class ModuleClass
{
    /**
     * @var string
     */
    private $class = ' ';

    /**
     * @var string|null
     */
    private $module;

    /**
     * @var string|null
     */
    private $dir;


    /**
     * Get class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set module.
     *
     * @param string|null $module
     *
     * @return ModuleClass
     */
    public function setModule($module = null)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module.
     *
     * @return string|null
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set dir.
     *
     * @param string|null $dir
     *
     * @return ModuleClass
     */
    public function setDir($dir = null)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * Get dir.
     *
     * @return string|null
     */
    public function getDir()
    {
        return $this->dir;
    }
}
