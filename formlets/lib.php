<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 + This program is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Helpers for external use.
 */

require_once("values.php");

function _collect() {
    $array = array();
    $collector = _fn_w(function(Value $v) use (&$array, &$collector) {
        if ( !($v->isError() || $v->isApplicable())
        &&   $v->get() instanceof Stop) {
            // Postprocessing of the collected values.
            // We need to check weather there are errors in the collection
            // to be able to get errors appropriately.
            $errors = array();
            $vals = array_map(function ($v) use (&$errors) {
                $v = $v->force();
                if ($v->isError()) {
                    $errors[] = $v;
                    return $v;
                }
                if ($v->isApplicable()) {
                    return $v;
                }  
                return $v->get();           
            }, $array);

            if (count($errors) > 0) {
                return _error("Collection contains errors.", "_collect", $errors);
            }
            return _val($vals, "collect");
        }

        $array[] = $v->force(); 
        return $collector;
    });
    return $collector;
}

/* Signals that the array is completed. */
class Stop {}
