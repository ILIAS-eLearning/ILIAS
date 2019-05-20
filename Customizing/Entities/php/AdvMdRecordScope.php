<?php



/**
 * AdvMdRecordScope
 */
class AdvMdRecordScope
{
    /**
     * @var int
     */
    private $scopeId = '0';

    /**
     * @var int
     */
    private $recordId = '0';

    /**
     * @var int
     */
    private $refId = '0';


    /**
     * Get scopeId.
     *
     * @return int
     */
    public function getScopeId()
    {
        return $this->scopeId;
    }

    /**
     * Set recordId.
     *
     * @param int $recordId
     *
     * @return AdvMdRecordScope
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;

        return $this;
    }

    /**
     * Get recordId.
     *
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return AdvMdRecordScope
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }
}
