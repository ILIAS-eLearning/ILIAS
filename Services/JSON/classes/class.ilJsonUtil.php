<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * JSON (Javascript Object Notation) functions with backward compatibility
 * for PHP version < 5.2
 *
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @deprecated use PHP native functions
 */
class ilJsonUtil
{

    /**
     * @param $mixed
     * @param bool $suppress_native
     * @return string
     *
     * @deprecated use json_encode instead
     */
    public static function encode($mixed, $suppress_native = false)
    {
        return json_encode($mixed);
    }


    /**
     * @param $json_notated_string
     * @param bool $suppress_native
     * @return mixed
     *
     * @deprecated use json_decode instead
     */
    public static function decode($json_notated_string, $suppress_native = false)
    {
        return json_decode($json_notated_string);
    }
}
