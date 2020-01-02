<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Update class for step 3004
 */
class ilDBUpdate3004
{
    public static function createPathFromId($a_container_id, $a_name)
    {
        $max_exponent = 3;
        $st_factor = 100;
        
        $path = array();
        $found = false;
        $num = $a_container_id;
        for ($i = $max_exponent; $i > 0;$i--) {
            $factor = pow($st_factor, $i);
            if (($tmp = (int) ($num / $factor)) or $found) {
                $path[] = $tmp;
                $num = $num % $factor;
                $found = true;
            }
        }

        if (count($path)) {
            $path_string = (implode('/', $path) . '/');
        }
        return $path_string . $a_name . '_' . $a_container_id;
    }
}
