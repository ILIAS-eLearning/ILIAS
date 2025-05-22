<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);
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
