<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilObjLTIConsumerVerificationAccess
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilObjLTIConsumerVerificationAccess extends ilObjectAccess
{
    /**
     * @return array<int, array<string, string|bool>>
     */
    public static function _getCommands() : array
    {
        $commands = array();
        $commands[] = array("permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true);
        return $commands;
    }

    public static function _checkGoto(string $target) : bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $t_arr = explode("_", $target);
        
        // #11021
        // personal workspace context: do not force normal login
        if (isset($t_arr[2]) && $t_arr[2] == "wsp") {
            return ilSharedResourceGUI::hasAccess((int) $t_arr[1]);
        }
        return (bool) $DIC->access()->checkAccess("read", "", (int) $t_arr[1]);
    }
}
