<?php



/**
 * PdfgenRendererAvail
 */
class PdfgenRendererAvail
{
    /**
     * @var int
     */
    private $availabilityId = '0';

    /**
     * @var string
     */
    private $service = '';

    /**
     * @var string
     */
    private $purpose = '';

    /**
     * @var string
     */
    private $renderer = '';


    /**
     * Get availabilityId.
     *
     * @return int
     */
    public function getAvailabilityId()
    {
        return $this->availabilityId;
    }

    /**
     * Set service.
     *
     * @param string $service
     *
     * @return PdfgenRendererAvail
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
     * @return PdfgenRendererAvail
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
     * Set renderer.
     *
     * @param string $renderer
     *
     * @return PdfgenRendererAvail
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
}
