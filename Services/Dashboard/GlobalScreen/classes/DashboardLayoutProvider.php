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

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\UI\Component\MainControls\MainBar;

/**
 * Class DashboardLayoutProvider
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class DashboardLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{
    /**
     * @var Collection | null
     */
    protected $data_collection;

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->desktop();
    }

    public function getMainBarModification(CalledContexts $screen_context_stack) : ?MainBarModification
    {
        $this->data_collection = $screen_context_stack->current()->getAdditionalData();
        if (!$this->data_collection->is(\ilDashboardGUI::DISENGAGE_MAINBAR, true)) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->mainbar()
            ->withModification(
                function (?MainBar $mainbar = null) : ?MainBar {
                    return $mainbar !== null ? $mainbar->withActive($mainbar::NONE_ACTIVE) : null;
                }
            )
            ->withLowPriority();
    }
}
