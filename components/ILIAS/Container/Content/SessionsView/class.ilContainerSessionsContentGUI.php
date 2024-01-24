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

/**
 * Shows all items in one block.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerSessionsContentGUI extends ilContainerContentGUI
{
    protected array $visible_sessions = [];
    protected bool $session_link_next = false;
    protected bool $session_link_prev = false;
    protected bool $session_limitation_initialised = false;

    protected function initRenderer(): void
    {
        parent::initRenderer();
        $this->renderer->setBlockPostfixClosure(function (string $type) {
            return $this->getBlockPostfix($type);
        });
        $this->renderer->setBlockPrefixClosure(function (string $type) {
            return $this->getBlockPrefix($type);
        });
        $this->renderer->setItemHiddenClosure(function (string $type, int $ref_id) {
            return $this->isItemHidden($type, $ref_id);
        });
    }

    protected function getBlockPostfix(string $type): string
    {
        if ($type === "sess") {
            $this->initSessionPresentationLimitation();
            if ($this->session_link_next) {
                return (string) $this->renderSessionLimitLink(false);
            }
        }
        return "";
    }

    protected function getBlockPrefix(string $type): string
    {
        if ($type === "sess") {
            $this->initSessionPresentationLimitation();
            if ($this->session_link_prev) {
                return (string) $this->renderSessionLimitLink(true);
            }
        }
        return "";
    }

    public function renderItemList(): string
    {
        $this->initRenderer();
        return $this->renderer->renderItemBlockSequence($this->item_presentation->getItemBlockSequence());
    }

    protected function renderSessionLimitLink(
        bool $a_previous = true
    ): string {
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        $lng->loadLanguageModule('crs');

        $tpl = new ilTemplate(
            'tpl.container_list_item.html',
            true,
            true,
            "components/ILIAS/Container"
        );
        $tpl->setVariable('DIV_CLASS', 'ilContainerListItemOuter');
        $tpl->setCurrentBlock('item_title_linked');

        if ($a_previous) {
            $prefp = $ilUser->getPref('crs_sess_show_prev_' . $this->getContainerObject()->getId());

            if ($prefp) {
                $tpl->setVariable('TXT_TITLE_LINKED', $lng->txt('crs_link_hide_prev_sessions'));
                $ilCtrl->setParameterByClass(get_class($this->getContainerGUI()), 'crs_prev_sess', (int) !$prefp);
                $tpl->setVariable('HREF_TITLE_LINKED', $ilCtrl->getLinkTargetByClass(get_class($this->getContainerGUI()), 'view'));
            } else {
                $tpl->setVariable('TXT_TITLE_LINKED', $lng->txt('crs_link_show_all_prev_sessions'));
                $ilCtrl->setParameterByClass(get_class($this->getContainerGUI()), 'crs_prev_sess', (int) !$prefp);
                $tpl->setVariable('HREF_TITLE_LINKED', $ilCtrl->getLinkTargetByClass(get_class($this->getContainerGUI()), 'view'));
            }
            $ilCtrl->clearParametersByClass(get_class($this->getContainerGUI()));
        } else {
            $prefn = $ilUser->getPref('crs_sess_show_next_' . $this->getContainerObject()->getId());

            if ($prefn) {
                $tpl->setVariable('TXT_TITLE_LINKED', $lng->txt('crs_link_hide_next_sessions'));
            } else {
                $tpl->setVariable('TXT_TITLE_LINKED', $lng->txt('crs_link_show_all_next_sessions'));
            }
            $ilCtrl->setParameterByClass(get_class($this->getContainerGUI()), 'crs_next_sess', (int) !$prefn);
            $tpl->setVariable('HREF_TITLE_LINKED', $ilCtrl->getLinkTargetByClass(get_class($this->getContainerGUI()), 'view'));
            $ilCtrl->clearParametersByClass(get_class($this->getContainerGUI()));
        }
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }


    protected function initSessionPresentationLimitation(): void
    {
        global $DIC;
        if ($this->session_limitation_initialised) {
            return;
        }
        $this->session_limitation_initialised = true;

        $container = $this->container_obj;
        $mode_manager = $DIC->container()->internal()->domain()->content()->mode($container);
        $session_ref_ids = $this->item_presentation->getRefIdsOfType("sess");

        $user = $DIC->user();
        $access = $DIC->access();
        $tree = $DIC->repositoryTree();
        $request = $DIC->container()
            ->internal()
            ->gui()
            ->standardRequest();

        $limit_sessions = false;
        if (!$mode_manager->isAdminMode() &&
            count($session_ref_ids) > 0 &&
            $container->isSessionLimitEnabled()) {
            $limit_sessions = true;
        }

        if ($container->getViewMode() === ilContainer::VIEW_INHERIT) {
            $parent = $tree->checkForParentType($container->getRefId(), 'crs');
            $crs = ilObjectFactory::getInstanceByRefId($parent, false);
            if (!$crs instanceof ilObjCourse) {
                return;
            }

            if (!$container->isSessionLimitEnabled()) {
                $limit_sessions = false;
            }
            $limit_next = $crs->getNumberOfNextSessions();
            $limit_prev = $crs->getNumberOfPreviousSessions();
        } else {
            $limit_next = $container->getNumberOfNextSessions();
            $limit_prev = $container->getNumberOfPreviousSessions();
        }

        if (!$limit_sessions) {
            $this->visible_sessions = $session_ref_ids;
            return;
        }

        // do session limit
        if ($request->getPreviousSession() !== null) {
            $user->writePref(
                'crs_sess_show_prev_' . $container->getId(),
                (string) $request->getPreviousSession()
            );
        }
        if ($request->getNextSession() !== null) {
            $user->writePref(
                'crs_sess_show_next_' . $container->getId(),
                (string) $request->getNextSession()
            );
        }

        $sessions = array_map(function ($ref_id) {
            return $this->item_presentation->getRawDataByRefId($ref_id);
        }, $session_ref_ids);
        $sessions = ilArrayUtil::sortArray($sessions, 'start', 'ASC', true, false);
        //$sessions = ilUtil::sortArray($this->items['sess'],'start','ASC',true,false);
        $today = new ilDate(date('Ymd'), IL_CAL_DATE);
        $previous = $current = $next = [];
        foreach ($sessions as $key => $item) {
            $start = new ilDateTime($item['start'], IL_CAL_UNIX);
            $end = new ilDateTime($item['end'], IL_CAL_UNIX);

            if (ilDateTime::_within($today, $start, $end, IL_CAL_DAY)) {
                $current[] = $item;
            } elseif (ilDateTime::_before($start, $today, IL_CAL_DAY)) {
                $previous[] = $item;
            } elseif (ilDateTime::_after($start, $today, IL_CAL_DAY)) {
                $next[] = $item;
            }
        }

        $num_previous_remove = max(
            count($previous) - $limit_prev,
            0
        );
        while ($num_previous_remove--) {
            if (!$user->getPref('crs_sess_show_prev_' . $container->getId())) {
                array_shift($previous);
            }
            $this->session_link_prev = true;
        }

        $num_next_remove = max(
            count($next) - $limit_next,
            0
        );
        while ($num_next_remove--) {
            if (!$user->getPref('crs_sess_show_next_' . $container->getId())) {
                array_pop($next);
            }
            // @fixme
            $this->session_link_next = true;
        }

        $sessions = array_merge($previous, $current, $next);
        $this->visible_sessions = array_map(static function ($item) {
            return (int) $item["ref_id"];
        }, $sessions);
    }

    protected function isItemHidden(string $type, int $ref_id): bool
    {
        if ($type === "sess") {
            $this->initSessionPresentationLimitation();
            return !in_array($ref_id, $this->visible_sessions, true);
        }
        return false;
    }

}
