<?php



/**
 * CtrlClassfile
 */
class CtrlClassfile
{
    /**
     * @var string
     */
    private $class = ' ';

    /**
     * @var string|null
     */
    private $filename;

    /**
     * @var string|null
     */
    private $compPrefix;

    /**
     * @var string|null
     */
    private $pluginPath;

    /**
     * @var string|null
     */
    private $cid;


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
     * Set filename.
     *
     * @param string|null $filename
     *
     * @return CtrlClassfile
     */
    public function setFilename($filename = null)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set compPrefix.
     *
     * @param string|null $compPrefix
     *
     * @return CtrlClassfile
     */
    public function setCompPrefix($compPrefix = null)
    {
        $this->compPrefix = $compPrefix;

        return $this;
    }

    /**
     * Get compPrefix.
     *
     * @return string|null
     */
    public function getCompPrefix()
    {
        return $this->compPrefix;
    }

    /**
     * Set pluginPath.
     *
     * @param string|null $pluginPath
     *
     * @return CtrlClassfile
     */
    public function setPluginPath($pluginPath = null)
    {
        $this->pluginPath = $pluginPath;

        return $this;
    }

    /**
     * Get pluginPath.
     *
     * @return string|null
     */
    public function getPluginPath()
    {
        return $this->pluginPath;
    }

    /**
     * Set cid.
     *
     * @param string|null $cid
     *
     * @return CtrlClassfile
     */
    public function setCid($cid = null)
    {
        $this->cid = $cid;

        return $this;
    }

    /**
     * Get cid.
     *
     * @return string|null
     */
    public function getCid()
    {
        return $this->cid;
    }
}
