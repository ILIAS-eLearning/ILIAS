<?php



/**
 * IlWacSecurePath
 */
class IlWacSecurePath
{
    /**
     * @var string
     */
    private $path = ' ';

    /**
     * @var string|null
     */
    private $componentDirectory;

    /**
     * @var string|null
     */
    private $checkingClass;

    /**
     * @var bool|null
     */
    private $inSecFolder;


    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set componentDirectory.
     *
     * @param string|null $componentDirectory
     *
     * @return IlWacSecurePath
     */
    public function setComponentDirectory($componentDirectory = null)
    {
        $this->componentDirectory = $componentDirectory;

        return $this;
    }

    /**
     * Get componentDirectory.
     *
     * @return string|null
     */
    public function getComponentDirectory()
    {
        return $this->componentDirectory;
    }

    /**
     * Set checkingClass.
     *
     * @param string|null $checkingClass
     *
     * @return IlWacSecurePath
     */
    public function setCheckingClass($checkingClass = null)
    {
        $this->checkingClass = $checkingClass;

        return $this;
    }

    /**
     * Get checkingClass.
     *
     * @return string|null
     */
    public function getCheckingClass()
    {
        return $this->checkingClass;
    }

    /**
     * Set inSecFolder.
     *
     * @param bool|null $inSecFolder
     *
     * @return IlWacSecurePath
     */
    public function setInSecFolder($inSecFolder = null)
    {
        $this->inSecFolder = $inSecFolder;

        return $this;
    }

    /**
     * Get inSecFolder.
     *
     * @return bool|null
     */
    public function getInSecFolder()
    {
        return $this->inSecFolder;
    }
}
