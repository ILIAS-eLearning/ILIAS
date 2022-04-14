<?php declare(strict_types=1);

namespace ILIAS\Notifiactions\Provider;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilObjNotificationAdmin;

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class NotificationMainBarProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $items = [];
        $access_helper = BasicAccessCheckClosuresSingleton::getInstance();
        $top = StandardTopItemsProvider::getInstance()->getAdministrationIdentification();

        $title = $this->dic->language()->txt("notifications");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('nota', $title)
                            ->withIsOutlined(true);

        $items[] = $this->mainmenu->link($this->if->identifier('mm_adm_nota'))
                                  ->withAlwaysAvailable(true)
                                  ->withAction(
                                      "ilias.php?baseClass=ilAdministrationGUI&ref_id=" .
                                      (new ilObjNotificationAdmin())->getRootRefId() .
                                      "&cmd=jump"
                                  )
                                  ->withNonAvailableReason(
                                      $this->dic->ui()->factory()->legacy(
                                          $this->dic->language()->txt('item_must_be_always_active')
                                      )
                                  )
                                  ->withParent($top)
                                  ->withTitle($title)
                                  ->withSymbol($icon)
                                  ->withPosition(6)
                                  ->withVisibilityCallable($access_helper->hasAdministrationAccess());

        return $items;
    }
}
