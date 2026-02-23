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

declare(strict_types=1);

namespace ILIAS\GlobalScreen\GUI\Tabs;

use ILIAS\Data\URI;
use ILIAS\GlobalScreen\GUI\I18n\Translator;
use ILIAS\GlobalScreen\GUI\Flow\Flow;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class Tabs
{
    private Structure $structure;
    private ?string $active_tab = null;
    private ?string $active_subtab = null;

    public function __construct(
        private Translator $translator,
        private \ilCtrlInterface $ctrl,
        private \ilTabsGUI $tabs_gui,
        private Flow $flow,
    ) {
        $this->structure = new Structure();
    }

    public function activate(string $tab_id): void
    {
        $tab = $this->structure->getById($tab_id);
        if ($tab === null) {
            return;
        }
        if ($tab->getParent() === null) {
            $this->active_tab = $tab_id;
        } else {
            $this->active_subtab = $tab_id;
            $this->active_tab = $tab->getParent()->getId();
        }
    }

    public function build(
        string $id,
        string $language_key,
        array|URI $target,
        ?Tab $parent = null,
        ?string $handling_class = null,
    ): Tab {
        if ($handling_class === null) {
            if ($target instanceof URI) {
                throw new \InvalidArgumentException('No handling class given');
            }
            if (is_array($target[0])) {
                // get last element of array without changing the array
                $handling_class = $target[0][count($target[0]) - 1];
            } elseif (is_string($target[0])) {
                $handling_class = $target[0];
            } else {
                throw new \InvalidArgumentException('No handling class given');
            }
        }

        if ($handling_class === null) {
            throw new \InvalidArgumentException('No handling class given');
        }
        if (!$target instanceof URI) {
            $target_class = $target[0];
            $command = $target[1] ?? null;
            //
            //            if ($parent !== null && !is_array($target_class)) {
            //                $flow = $this->flow->getHereAsFlow();
            //                $flow[] = $target_class;
            //            } else {
            //                $flow = $target_class;
            //            }

            $uri = $this->flow->getTargetURI($target_class, $command);
        } else {
            $uri = $target;
        }

        return new Tab(
            $id,
            $language_key,
            $uri,
            $handling_class,
            $parent
        );
    }

    public function show(): void
    {
        $this->tabs_gui->clearTargets();

        $current_tab = $this->structure->getByHandlingClass(
            $this->flow->getCurrentClass() ?? ''
        );

        if (
            $current_tab !== null
            && $current_tab->getParent() !== null
            && $current_tab->getParent()->getParent() !== null
        ) {
            // add backtab
            $this->tabs_gui->setBackTarget(
                $this->translator->translate('back'),
                $this->ctrl->getLinkTargetByClass(
                    $current_tab->getParent()->getHandlingClass()
                )
            );
            return;
        }

        foreach ($this->structure->get() as $tab) {
            if ($tab->getParent() === null) {
                $this->tabs_gui->addTab(
                    $tab->getId(),
                    $this->translator->t($tab->getLanguageKey()),
                    (string) $tab->getTarget()
                );
            } elseif ($tab->getParent()->getId() === $this->active_tab) {
                $this->tabs_gui->addSubTab(
                    $tab->getId(),
                    $this->translator->t($tab->getLanguageKey()),
                    (string) $tab->getTarget()
                );
            }
        }
        if ($this->active_tab !== null) {
            $this->tabs_gui->activateTab($this->active_tab);
        }
        if ($this->active_subtab !== null) {
            $this->tabs_gui->activateSubTab($this->active_subtab);
        }
    }

    public function structure(): Structure
    {
        return $this->structure;
    }

    public function add(Tab ...$tab): void
    {
        foreach ($tab as $t) {
            $this->structure->add($t);
        }
    }

    public function backToMainTab(): void
    {
        $this->tabs_gui->clearTargets();

        if (
            $this->ctrl->getCmd(ilFooterEntriesGUI::CMD_DEFAULT) !== ilFooterEntriesGUI::CMD_DEFAULT
            && $this->ctrl->getCmdClass() === strtolower(ilFooterEntriesGUI::class)
        ) {
            $this->tabs_gui->setBackTarget(
                $this->translator->translate('back'),
                $this->ctrl->getLinkTargetByClass(
                    ilFooterEntriesGUI::class,
                    ilFooterEntriesGUI::CMD_DEFAULT
                )
            );
            return;
        }

        $this->tabs_gui->setBackTarget(
            $this->translator->translate('back'),
            $this->ctrl->getLinkTargetByClass(
                ilObjFooterAdministrationGUI::class,
                ilObjFooterAdministrationGUI::CMD_DEFAULT
            )
        );
    }
}
