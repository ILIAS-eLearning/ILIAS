<?php



/**
 * TaxUsage
 */
class TaxUsage
{
    /**
     * @var int
     */
    private $taxId = '0';

    /**
     * @var int
     */
    private $objId = '0';


    /**
     * Set taxId.
     *
     * @param int $taxId
     *
     * @return TaxUsage
     */
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;

        return $this;
    }

    /**
     * Get taxId.
     *
     * @return int
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return TaxUsage
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }
}
