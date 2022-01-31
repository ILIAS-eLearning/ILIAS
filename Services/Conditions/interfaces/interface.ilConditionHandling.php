<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for condition handling
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
interface ilConditionHandling
{

    /**
     * Returns an array with valid operators for the specific object type
     * @return string[]
     */
    public static function getConditionOperators() : array;

    /**
     * check condition for a specific user and object
     */
    public static function checkCondition(
        int $a_trigger_obj_id,
        string $a_operator,
        string $a_value,
        int $a_usr_id
    ) : bool;
}
