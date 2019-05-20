<?php



/**
 * PdfgenMap
 */
class PdfgenMap
{
    /**
     * @var int
     */
    private $mapId = '0';

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
    private $preferred = '';

    /**
     * @var string
     */
    private $selected = '';


    /**
     * Get mapId.
     *
     * @return int
     */
    public function getMapId()
    {
        return $this->mapId;
    }

    /**
     * Set service.
     *
     * @param string $service
     *
     * @return PdfgenMap
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
     * @return PdfgenMap
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
     * Set preferred.
     *
     * @param string $preferred
     *
     * @return PdfgenMap
     */
    public function setPreferred($preferred)
    {
        $this->preferred = $preferred;

        return $this;
    }

    /**
     * Get preferred.
     *
     * @return string
     */
    public function getPreferred()
    {
        return $this->preferred;
    }

    /**
     * Set selected.
     *
     * @param string $selected
     *
     * @return PdfgenMap
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Get selected.
     *
     * @return string
     */
    public function getSelected()
    {
        return $this->selected;
    }
}
