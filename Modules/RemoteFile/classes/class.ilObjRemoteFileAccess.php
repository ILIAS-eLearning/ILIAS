<?php

declare(strict_types=1);

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
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ModulesRemoteFile
*/

class ilObjRemoteFileAccess extends ilObjectAccess
{
    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *	(
     *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *	);
     */
    public static function _getCommands(): array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "show", "lang_var" => "info",
                "default" => true),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "edit")
        );

        return $commands;
    }
}
