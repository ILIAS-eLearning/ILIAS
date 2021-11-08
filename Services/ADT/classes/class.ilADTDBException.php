<?php declare(strict_types=1);

class ilADTDBException extends ilException
{
    protected string $col = '';

    public function getColumn() : string
    {
        return $this->col;
    }

    public function setColumn(string $a_col) : void
    {
        $this->col = $a_col;
    }
}
