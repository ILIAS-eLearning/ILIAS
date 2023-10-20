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

namespace ILIAS\Container\Content;

/**
 * Acts on single items
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemManager
{
    protected \ILIAS\Container\StandardGUIRequest $request;
    protected array $force_details = [];
    protected ModeManager $mode_manager;
    protected \ilContainer $container;
    protected ItemSessionRepository $item_repo;

    public function __construct(
        \ilContainer $container,
        ItemSessionRepository $item_repo,
        ModeManager $mode_manager,
        \ILIAS\Container\StandardGUIRequest $request
    ) {
        $this->item_repo = $item_repo;
        $this->container = $container;
        $this->mode_manager = $mode_manager;
        $this->request = $request;
    }

    /**
     * @todo this is GUI class responsibility, might go to top of ilContainerContentGUI
     */
    protected function handleSessionExpand(): void
    {
        $expand = $this->request->getExpand();
        if ($expand > 0) {
            $this->setExpanded(abs($expand), \ilContainerContentGUI::DETAILS_ALL);
        } elseif ($expand < 0) {
            $this->setExpanded(abs($expand), \ilContainerContentGUI::DETAILS_TITLE);
        }
    }

    protected function init(): void
    {
        $this->handleSessionExpand();

        if ($this->container->getType() === 'crs') {
            if ($session = \ilSessionAppointment::lookupNextSessionByCourse($this->container->getRefId())) {
                $this->force_details = $session;
            } elseif ($session = \ilSessionAppointment::lookupLastSessionByCourse($this->container->getRefId())) {
                $this->force_details = [$session];
            }
        }
    }

    public function setExpanded(int $id, int $val): void
    {
        $this->item_repo->setExpanded($id, $val);
    }

    public function getExpanded(int $id): ?int
    {
        return $this->item_repo->getExpanded($id);
    }

    protected function getDetailsLevel(int $a_item_id): int
    {
        if ($this->mode_manager->isAdminMode()) {
            return \ilContainerContentGUI::DETAILS_DEACTIVATED;
        }
        if ($this->getExpanded($a_item_id) !== null) {
            return $this->getExpanded($a_item_id);
        }
        if (in_array($a_item_id, $this->force_details)) {
            return \ilContainerContentGUI::DETAILS_ALL;
        }
        return \ilContainerContentGUI::DETAILS_TITLE;
    }
}
