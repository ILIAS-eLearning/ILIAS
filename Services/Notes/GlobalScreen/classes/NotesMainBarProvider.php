<?php

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

namespace ILIAS\Notes\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;

/**
 * Class NotesMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotesMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems() : array
    {
        return [];
    }

    public function getStaticSubItems() : array
    {
        $dic = $this->dic;
        $ctrl = $dic->ctrl();

        // Comments
        $title = $dic->language()->txt("mm_comments");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::COMS, $title);
        $comments = $this->mainmenu->link($this->if->identifier('mm_pd_comments'))
            ->withTitle($title)
            ->withAction($ctrl->getLinkTargetByClass(["ilDashboardGUI", "ilPDNotesGUI"], "showPublicComments"))
            ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
            ->withPosition(50)
            ->withSymbol($icon)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy($this->dic->language()->txt('component_not_active')))
            ->withAvailableCallable(
                static function () use ($dic) : bool {
                    return !$dic->settings()->get("disable_comments");
                }
            );

        $title = $dic->language()->txt("mm_notes");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::NOTS, $title);

        // Notes
        $notes = $this->mainmenu->link($this->if->identifier('mm_pd_notes'))
            ->withTitle($title)
            ->withAction($ctrl->getLinkTargetByClass(["ilDashboardGUI", "ilPDNotesGUI"], "showPrivateNotes"))
            ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
            ->withPosition(70)
            ->withSymbol($icon)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy($this->dic->language()->txt('component_not_active')))
            ->withAvailableCallable(
                static function () use ($dic) : bool {
                    return !$dic->settings()->get("disable_notes");
                }
            );

        return [
            $comments,
            $notes,
        ];
    }
}
