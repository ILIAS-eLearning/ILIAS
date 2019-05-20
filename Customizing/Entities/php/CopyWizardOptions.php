<?php



/**
 * CopyWizardOptions
 */
class CopyWizardOptions
{
    /**
     * @var int
     */
    private $copyId = '0';

    /**
     * @var int
     */
    private $sourceId = '0';

    /**
     * @var string|null
     */
    private $options;


    /**
     * Set copyId.
     *
     * @param int $copyId
     *
     * @return CopyWizardOptions
     */
    public function setCopyId($copyId)
    {
        $this->copyId = $copyId;

        return $this;
    }

    /**
     * Get copyId.
     *
     * @return int
     */
    public function getCopyId()
    {
        return $this->copyId;
    }

    /**
     * Set sourceId.
     *
     * @param int $sourceId
     *
     * @return CopyWizardOptions
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    /**
     * Get sourceId.
     *
     * @return int
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Set options.
     *
     * @param string|null $options
     *
     * @return CopyWizardOptions
     */
    public function setOptions($options = null)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options.
     *
     * @return string|null
     */
    public function getOptions()
    {
        return $this->options;
    }
}
