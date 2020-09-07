<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for condition handling
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
interface ilConditionHandling
{

    /**
     * Returns an array with valid operators for the specific object type
     */
    public static function getConditionOperators();

    /**
     * check condition for a specific user and object
     * @param type $a_trigger_obj_id
     * @param type $a_operator
     * @param type $a_value
     * @param type $a_usr_id
     */
    public static function checkCondition($a_trigger_obj_id, $a_operator, $a_value, $a_usr_id);
}
