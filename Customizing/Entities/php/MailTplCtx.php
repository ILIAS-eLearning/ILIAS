<?php



/**
 * MailTplCtx
 */
class MailTplCtx
{
    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string
     */
    private $component = '';

    /**
     * @var string
     */
    private $class = '';

    /**
     * @var string|null
     */
    private $path;


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
     * Set component.
     *
     * @param string $component
     *
     * @return MailTplCtx
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
     * Set class.
     *
     * @param string $class
     *
     * @return MailTplCtx
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

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
     * Set path.
     *
     * @param string|null $path
     *
     * @return MailTplCtx
     */
    public function setPath($path = null)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }
}
