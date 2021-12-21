<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* List GUI factory for lucene search results
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilLuceneSearchObjectListGUIFactory
{
    private static array $item_list_gui = [];
    
    public static function factory(string $a_type) : ilObjectListGUI
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $container_view_manager = $DIC
            ->container()
            ->internal()
            ->domain()
            ->content()
            ->view();


        if (isset(self::$item_list_gui[$a_type])) {
            return self::$item_list_gui[$a_type];
        }


        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);

        $full_class = "ilObj" . $class . "ListGUI";

        include_once($location . "/class." . $full_class . ".php");
        $item_list_gui = new $full_class();

        $item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_SEARCH);
        $item_list_gui->enableDelete(true);
        $item_list_gui->enableCut(true);
        $item_list_gui->enableSubscribe(true);
        $item_list_gui->enableLink(true);
        $item_list_gui->enablePath(false);
        $item_list_gui->enableLinkedPath(true);
        $item_list_gui->enableSearchFragments(true);
        $item_list_gui->enableRelevance(false);
        if ($container_view_manager->isAdminView()) {
            $item_list_gui->enableCheckbox(false);
        }

        return self::$item_list_gui[$a_type] = $item_list_gui;
    }
}
