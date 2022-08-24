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

namespace ILIAS\Contact\Provider;

use ilBuddySystem;
use ilContactGUI;
use ilDashboardGUI;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;

/**
 * Class ContactMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContactMainBarProvider extends AbstractStaticMainMenuProvider
{
    /**
     * @inheritDoc
     */
    public function getStaticTopItems(): array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems(): array
    {
        $title = $this->dic->language()->txt("mm_contacts");

        $icon = $this->dic->ui()->factory()
            ->symbol()
            ->icon()
            ->standard(Standard::CADM, 'contacts');


        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_contacts'))
                ->withTitle($title)
                ->withAction($this->dic->ctrl()->getLinkTargetByClass([ilDashboardGUI::class, ilContactGUI::class]))
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(20)
                ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy($this->dic->language()->txt('component_not_active')))
                ->withAvailableCallable(
                    static function (): bool {
                        return ilBuddySystem::getInstance()->isEnabled();
                    }
                ),
        ];
    }
}
