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
        if (isset($_SESSION["ilEditClipboard"])) {
            return $_SESSION["ilEditClipboard"]["type"];
        } else {
            return false;
        }
    }

    public static function setAction($a_action)
    {
        $_SESSION["ilEditClipboard"] = array("action" => $a_action);
    }

    public static function getAction()
    {
        if (isset($_SESSION["ilEditClipboard"])) {
            return $_SESSION["ilEditClipboard"]["action"];
        } else {
            return false;
        }
    }

    public static function getContentObjectId()
    {
        if (isset($_SESSION["ilEditClipboard"])) {
            return $_SESSION["ilEditClipboard"]["id"];
        }
    }

    public static function storeContentObject($a_type, $a_id, $a_action = "cut")
    {
        $_SESSION["ilEditClipboard"] = array("type" => $a_type,
            "id" => $a_id, "action" => $a_action);
    }

    public static function clear()
    {
        unset($_SESSION["ilEditClipboard"]);
    }
}
