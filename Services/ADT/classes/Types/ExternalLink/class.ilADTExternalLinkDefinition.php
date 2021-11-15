<?php declare(strict_types=1);

class ilADTExternalLinkDefinition extends ilADTDefinition
{

    /**
     * is comparable to
     * @param ilADT $a_adt
     * @return bool
     */
    public function isComparableTo(ilADT $a_adt) : bool
    {
        return $a_adt instanceof ilADTExternalLink;
    }
}
