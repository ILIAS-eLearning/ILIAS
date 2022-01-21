<?php declare(strict_types=1);

/**
 * Class ilADTSearchBridgeRange
 */
abstract class ilADTSearchBridgeRange extends ilADTSearchBridge
{
    protected ilADT $adt_lower;
    protected ilADT $adt_upper;

    protected function setDefinition(ilADTDefinition $a_adt_def) : void
    {
        if ($this->isValidADTDefinition($a_adt_def)) {
            $factory = ilADTFactory::getInstance();
            $this->adt_lower = $factory->getInstanceByDefinition($a_adt_def);
            $this->adt_upper = $factory->getInstanceByDefinition($a_adt_def);
            return;
        }

        throw new InvalidArgumentException('ilADTSearchBridge type mismatch.');
    }

    /**
     * Get lower ADT
     * @return ilADT | null
     */
    public function getLowerADT() : ?ilADT
    {
        return $this->adt_lower;
    }

    /**
     * Get upper ADT
     * @return ilADT | null
     */
    public function getUpperADT() : ?ilADT
    {
        return $this->adt_upper;
    }

    public function isNull() : bool
    {
        if (!$this->getLowerADT() instanceof ilADT || !$this->getUpperADT() instanceof ilADT) {
            return false;
        }
        return ($this->getLowerADT()->isNull() && $this->getUpperADT()->isNull());
    }

    public function isValid() : bool
    {
        if (!$this->getLowerADT() instanceof ilADT || !$this->getUpperADT() instanceof ilADT) {
            return false;
        }
        return ($this->getLowerADT()->isValid() && $this->getUpperADT()->isValid());
    }

    public function validate() : bool
    {
        if (!$this->getLowerADT() instanceof ilADT || !$this->getUpperADT() instanceof ilADT) {
            return false;
        }
        if (!$this->isValid()) {
            $tmp = [];
            $mess = $this->getLowerADT()->getValidationErrors();
            foreach ($mess as $error_code) {
                $tmp[] = $this->getLowerADT()->translateErrorCode($error_code);
            }
            if ($tmp) {
                $field = $this->getForm()->getItemByPostVar($this->addToElementId("lower"));
                $field->setAlert(implode("<br />", $tmp));
            }

            $tmp = [];
            $mess = $this->getUpperADT()->getValidationErrors();
            foreach ($mess as $error_code) {
                $tmp[] = $this->getUpperADT()->translateErrorCode($error_code);
            }
            if ($tmp) {
                $field = $this->getForm()->getItemByPostVar($this->addToElementId("upper"));
                $field->setAlert(implode("<br />", $tmp));
            }
            return false;
        }
        return true;
    }
}
