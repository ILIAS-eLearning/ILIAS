<?php



/**
 * ServiceClass
 */
class ServiceClass
{
    /**
     * @var string
     */
    private $class = ' ';

    /**
     * @var string|null
     */
    private $service;

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
     * Set service.
     *
     * @param string|null $service
     *
     * @return ServiceClass
     */
    public function setService($service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service.
     *
     * @return string|null
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set dir.
     *
     * @param string|null $dir
     *
     * @return ServiceClass
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
