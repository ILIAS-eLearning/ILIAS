<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\PersonalDesktop;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class PDMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PDMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems(): array
    {
        return [];
    }

    public function getStaticSubItems(): array
    {
        $items = [
        ];

        $top = StandardTopItemsProvider::getInstance()->getAdministrationIdentification();

        $title = $this->dic->language()->txt("obj_dshs");
        $objects_by_type = \ilObject2::_getObjectsByType('dshs');
        $id = (int) reset($objects_by_type)['obj_id'];
        $references = \ilObject2::_getAllReferences($id);
        $admin_ref_id = (int) reset($references);

        if ($admin_ref_id > 0) {
            $action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . $admin_ref_id . "&cmd=jump";
            $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("dshs", $title);

            $items[] = $this->mainmenu->link($this->if->identifier('mm_adm_dshs'))
                                      ->withAction($action)
                                      ->withParent($top)
                                      ->withTitle($title)
                                      ->withSymbol($icon)
                                      ->withPosition(25)
                                      ->withVisibilityCallable(function () use ($admin_ref_id) {
                                          return $this->dic->rbac()->system()->checkAccess(
                                              'visible,read',
                                              $admin_ref_id
                                          );
                                      });
        }


        return $items;
    }
}
