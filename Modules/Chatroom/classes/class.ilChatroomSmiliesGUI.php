<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSmiliesGUI
 * Chat smiley GUI handler
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSmiliesGUI
{
    public static function _getExistingSmiliesTable(ilChatroomObjectGUI $a_ref) : string
    {
        $table = new ilChatroomSmiliesTableGUI($a_ref, 'smiley');
        $values = ilChatroomSmilies::_getSmilies();
        $table->setData($values);

        return $table->getHTML();
    }
}
