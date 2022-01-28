<?php declare(strict_types=1);

class ilADTFloatDefinition extends ilADTIntegerDefinition
{
    protected int $decimals;

    public function reset() : void
    {
        parent::reset();
        $this->setDecimals(1);
    }

    // properties

    public function handleNumber(int $a_value) : int
    {
        if (!is_numeric($a_value)) {
            $a_value = null;
        }
        if ($a_value !== null) {
            $a_value = round((float) $a_value, $this->getDecimals());
        }
        return $a_value;
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
