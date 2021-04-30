<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Class ilEditClipboard
*
* This class supports only a few basic clipboard functions for the
* editor and should be further elaborated in the future.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilEditClipboard
{
    public static function getContentObjectType()
    {
        global $DIC;
        $user = $DIC->user();
        $lm_type = $user->getPref("lm_clipboard_type");
        if ($lm_type != "") {
            return $lm_type;
        } else {
            return false;
        }
    }

    public static function setAction($a_action)
    {
        global $DIC;
        $user = $DIC->user();
        $user->writePref("lm_clipboard_action", $a_action);
    }

    public static function getAction()
    {
        global $DIC;
        $user = $DIC->user();
        $lm_action = $user->getPref("lm_clipboard_action");
        if ($lm_action != "") {
            return $lm_action;
        } else {
            return false;
        }
    }

    public static function getContentObjectId()
    {
        global $DIC;
        $user = $DIC->user();
        $lm_id = $user->getPref("lm_clipboard_id");
        if ($lm_id != "") {
            return $lm_id;
        }
        return "";
    }

    public static function storeContentObject($a_type, $a_id, $a_action = "cut")
    {
        global $DIC;
        $user = $DIC->user();
        $user->writePref("lm_clipboard_id", $a_id);
        $user->writePref("lm_clipboard_type", $a_type);
        self::setAction($a_action);
    }

    public static function clear()
    {
        global $DIC;
        $user = $DIC->user();
        $user->clipboardDeleteObjectsOfType("pg");
        $user->clipboardDeleteObjectsOfType("st");
        $user->writePref("lm_clipboard_id", "");
        $user->writePref("lm_clipboard_type", "");
        $user->writePref("lm_clipboard_action", "");
    }
}
