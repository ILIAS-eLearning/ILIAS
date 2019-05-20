<?php



/**
 * PdfgenConf
 */
class PdfgenConf
{
    /**
     * @var int
     */
    private $confId = '0';

    /**
     * @var string
     */
    private $renderer = '';

    /**
     * @var string
     */
    private $service = '';

    /**
     * @var string
     */
    private $purpose = '';

    /**
     * @var string|null
     */
    private $config;


    /**
     * Get confId.
     *
     * @return int
     */
    public function getConfId()
    {
        return $this->confId;
    }

    /**
     * Set renderer.
     *
     * @param string $renderer
     *
     * @return PdfgenConf
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Get renderer.
     *
     * @return string
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Set service.
     *
     * @param string $service
     *
     * @return PdfgenConf
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service.
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set purpose.
     *
     * @param string $purpose
     *
     * @return PdfgenConf
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * Get purpose.
     *
     * @return string
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Set config.
     *
     * @param string|null $config
     *
     * @return PdfgenConf
     */
    public function setConfig($config = null)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config.
     *
     * @return string|null
     */
    public function getConfig()
    {
        return $this->config;
    }
}
