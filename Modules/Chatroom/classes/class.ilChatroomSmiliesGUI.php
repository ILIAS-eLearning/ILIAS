<?php

declare(strict_types=1);

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
 * Class ilChatroomSmiliesGUI
 * Chat smiley GUI handler
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSmiliesGUI
{
    public static function _getExistingSmiliesTable(ilChatroomObjectGUI $a_ref): string
    {
        $table = new ilChatroomSmiliesTableGUI($a_ref, 'smiley');
        $values = ilChatroomSmilies::_getSmilies();
        $table->setData($values);

        return $table->getHTML();
    }
}
