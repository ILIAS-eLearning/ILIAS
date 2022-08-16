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
        $item_list_gui = new $full_class(ilObjectListGUI::CONTEXT_SEARCH);

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
