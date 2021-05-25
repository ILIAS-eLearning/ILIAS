<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * JSON (Javascript Object Notation) functions with backward compatibility
 * for PHP version < 5.2
 *
 * @author  Jan Posselt <jposselt@databay.de>
 * @deprecated Use PHP native functions
 */
class ilJsonUtil
{
    public static function encode($mixed) : string
    {
        return json_encode($mixed);
    }

    public static function decode(string $json_notated_string)
    {
        return json_decode($json_notated_string);
    }
}
