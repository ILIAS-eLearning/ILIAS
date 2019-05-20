<?php



/**
 * PdfgenPurposes
 */
class PdfgenPurposes
{
    /**
     * @var int
     */
    private $purposeId = '0';

    /**
     * @var string
     */
    private $service = '';

    /**
     * @var string
     */
    private $purpose = '';


    /**
     * Get purposeId.
     *
     * @return int
     */
    public function getPurposeId()
    {
        return $this->purposeId;
    }

    /**
     * Set service.
     *
     * @param string $service
     *
     * @return PdfgenPurposes
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
     * @return PdfgenPurposes
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
}
