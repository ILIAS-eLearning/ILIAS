<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT definition base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
abstract class ilADTDefinition
{
    protected bool $allow_null;

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Get type (from class/instance)
     * @return string
     */
    public function getType(): string
    {
        return substr(substr(get_class($this), 5), 0, -10);
    }

    /**
     * Init property defaults
     */
    public function reset(): void
    {
        $this->setAllowNull(true);
    }


    //
    // null
    //

    /**
     * Toggle null allowed status
     * @param bool $a_value
     */
    public function setAllowNull(bool $a_value): void
    {
        $this->allow_null = $a_value;
    }

    public function isNullAllowed(): bool
    {
        return $this->allow_null;
    }


    //
    // comparison
    //

    /**
     * Check if given ADT is comparable to self
     * @param ilADT $a_adt
     * @return bool
     */
    abstract public function isComparableTo(ilADT $a_adt): bool;
}
