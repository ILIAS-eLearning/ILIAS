<?php



/**
 * TaxNodeAssignment
 */
class TaxNodeAssignment
{
    /**
     * @var int
     */
    private $nodeId = '0';

    /**
     * @var string
     */
    private $component = '';

    /**
     * @var string
     */
    private $itemType = '';

    /**
     * @var int
     */
    private $itemId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $taxId = '0';

    /**
     * @var int
     */
    private $orderNr = '0';


    /**
     * Set nodeId.
     *
     * @param int $nodeId
     *
     * @return TaxNodeAssignment
     */
    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;

        return $this;
    }

    /**
     * Get nodeId.
     *
     * @return int
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * Set component.
     *
     * @param string $component
     *
     * @return TaxNodeAssignment
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
     * Set itemType.
     *
     * @param string $itemType
     *
     * @return TaxNodeAssignment
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;

        return $this;
    }

    /**
     * Get itemType.
     *
     * @return string
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * Set itemId.
     *
     * @param int $itemId
     *
     * @return TaxNodeAssignment
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * Get itemId.
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return TaxNodeAssignment
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

    /**
     * Set taxId.
     *
     * @param int $taxId
     *
     * @return TaxNodeAssignment
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
     * Set orderNr.
     *
     * @param int $orderNr
     *
     * @return TaxNodeAssignment
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get orderNr.
     *
     * @return int
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }
}
