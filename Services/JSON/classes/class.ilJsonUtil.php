<?php declare(strict_types=1);

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

/**
 * JSON (Javascript Object Notation) functions with backward compatibility
 * for PHP version < 5.2
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @deprecated Use PHP native functions
 */
class ilJsonUtil
{
    public static function encode($mixed) : string
    {
        return json_encode($mixed, JSON_THROW_ON_ERROR);
    }

    public static function decode(string $json_notated_string)
    {
        return json_decode($json_notated_string, false, 512, JSON_THROW_ON_ERROR);
    }
}
