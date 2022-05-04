<?php declare(strict_types=1);

class ilADTFloatDefinition extends ilADTDefinition
{
    protected int $decimals;
    protected ?float $min_value;
    protected ?float $max_value;
    protected string $suffix = '';
    
    // properties
    
    public function handleNumber($a_value) : ?float
    {
        if (!is_numeric($a_value)) {
            $a_value = null;
        }
        if ($a_value !== null) {
            // round?
            $a_value = (float) $a_value;
        }
        return $a_value;
    }
    
    public function getMin() : ?float
    {
        return $this->min_value;
    }
    
    public function setMin(?float $a_value) : void
    {
        $this->min_value = $this->handleNumber($a_value);
    }
    
    public function getMax() : ?float
    {
        return $this->max_value;
    }
    
    public function setMax(?float $a_value) : void
    {
        $this->max_value = $this->handleNumber($a_value);
    }
    
    public function getSuffix() : string
    {
        return $this->suffix;
    }
    
    public function setSuffix(?string $a_value) : void
    {
        $this->suffix = $a_value === null ? '' : trim($a_value);
    }
    
    public function isComparableTo(ilADT $a_adt) : bool
    {
        // has to be number-based
        return ($a_adt instanceof ilADTFloat);
    }
    
    public function reset() : void
    {
        parent::reset();
        $this->setDecimals(1);
    }
    
    public function getDecimals() : int
    {
        return $this->decimals;
    }
    
    public function setDecimals(int $a_value) : void
    {
        // max precision ?!
        $this->decimals = max(1, abs($a_value));
    }
}
