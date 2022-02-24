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

/**
 * Shows all items in one block.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerSessionsContentGUI extends ilContainerContentGUI
{
    protected ilTabsGUI $tabs;
    protected array $force_details = [];
    
    public function __construct(ilContainerGUI $container_gui_obj)
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        parent::__construct($container_gui_obj);
        $this->lng = $lng;
        $this->initDetails();
    }


    protected function getDetailsLevel(int $a_item_id) : int
    {
        if ($this->getContainerGUI()->isActiveAdministrationPanel()) {
            return self::DETAILS_DEACTIVATED;
        }
        if ($this->item_manager->getExpanded($a_item_id) !== null) {
            return $this->item_manager->getExpanded($a_item_id);
        }
        if (in_array($a_item_id, $this->force_details)) {
            return self::DETAILS_ALL;
        } else {
            return self::DETAILS_TITLE;
        }
    }

    public function getMainContent() : string
    {
        // see bug #7452
        //		$ilTabs->setSubTabActive($this->getContainerObject()->getType().'_content');

        $tpl = new ilTemplate(
            "tpl.container_page.html",
            true,
            true,
            "Services/Container"
        );

        $this->__showMaterials($tpl);
            
        return $tpl->get();
    }

    public function __showMaterials(ilTemplate $a_tpl) : void
    {
        $lng = $this->lng;

        $this->items = $this->getContainerObject()->getSubItems($this->getContainerGUI()->isActiveAdministrationPanel());
        $this->clearAdminCommandsDetermination();
        
        $this->initRenderer();
        
        $output_html = $this->getContainerGUI()->getContainerPageHTML();
        
        // get embedded blocks
        if ($output_html != "") {
            $output_html = $this->insertPageEmbeddedBlocks($output_html);
        }
        
        if (is_array($this->items["sess"]) ||
            isset($this->items['sess_link']['prev']['value']) ||
            isset($this->items['sess_link']['next']['value'])) {
            $this->items['sess'] = ilArrayUtil::sortArray($this->items['sess'], 'start', 'asc', true, false);

            $prefix = $postfix = "";
            if (isset($this->items['sess_link']['prev']['value'])) {
                $prefix = $this->renderSessionLimitLink(true);
            }
            if (isset($this->items['sess_link']['next']['value'])) {
                $postfix = $this->renderSessionLimitLink(false);
            }
            
            $this->renderer->addTypeBlock("sess", $prefix, $postfix);
            $this->renderer->setBlockPosition("sess", 1);
            
            $position = 1;
            
            foreach ($this->items["sess"] as $item_data) {
                if (!$this->renderer->hasItem($item_data["child"])) {
                    $html = $this->renderItem($item_data, $position++, true);
                    if ($html != "") {
                        $this->renderer->addItemToBlock("sess", $item_data["type"], $item_data["child"], $html);
                    }
                }
            }
        }

        $pos = $this->getItemGroupsHTML(1);
        
        if (is_array($this->items["_all"])) {
            $this->renderer->addCustomBlock("_all", $lng->txt("content"));
            $this->renderer->setBlockPosition("_all", ++$pos);
                        
            $position = 1;
            
            foreach ($this->items["_all"] as $item_data) {
                // #14599
                if ($item_data["type"] == "sess" || $item_data["type"] == "itgr") {
                    continue;
                }
                
                if (!$this->renderer->hasItem($item_data["child"])) {
                    $html = $this->renderItem($item_data, $position++, true);
                    if ($html != "") {
                        $this->renderer->addItemToBlock("_all", $item_data["type"], $item_data["child"], $html);
                    }
                }
            }
        }

        $output_html .= $this->renderer->getHTML();
        
        $a_tpl->setVariable("CONTAINER_PAGE_CONTENT", $output_html);
    }

    protected function renderSessionLimitLink(
        bool $a_previous = true
    ) : string {
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        
        $lng->loadLanguageModule('crs');

        $tpl = new ilTemplate(
            'tpl.container_list_item.html',
            true,
            true,
            "Services/Container"
        );
        $tpl->setVariable('DIV_CLASS', 'ilContainerListItemOuter');
        $tpl->setCurrentBlock('item_title_linked');

        if ($a_previous) {
            $prefp = $ilUser->getPref('crs_sess_show_prev_' . $this->getContainerObject()->getId());
            
            if ($prefp) {
                $tpl->setVariable('TXT_TITLE_LINKED', $lng->txt('crs_link_hide_prev_sessions'));
            } else {
                $tpl->setVariable('TXT_TITLE_LINKED', $lng->txt('crs_link_show_all_prev_sessions'));
            }
            $ilCtrl->setParameterByClass(get_class($this->getContainerGUI()), 'crs_prev_sess', (int) !$prefp);
        } else {
            $prefn = $ilUser->getPref('crs_sess_show_next_' . $this->getContainerObject()->getId());

            if ($prefn) {
                $tpl->setVariable('TXT_TITLE_LINKED', $lng->txt('crs_link_hide_next_sessions'));
            } else {
                $tpl->setVariable('TXT_TITLE_LINKED', $lng->txt('crs_link_show_all_next_sessions'));
            }
            $ilCtrl->setParameterByClass(get_class($this->getContainerGUI()), 'crs_next_sess', (int) !$prefn);
        }
        $tpl->setVariable('HREF_TITLE_LINKED', $ilCtrl->getLinkTargetByClass(get_class($this->getContainerGUI())));
        $ilCtrl->clearParametersByClass(get_class($this->getContainerGUI()));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }
    
    
    public function addFooterRow(
        ilTemplate $tpl
    ) : void {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass(
            "ilrepositorygui",
            "ref_id",
            $this->request->getRefId()
        );
        
        $tpl->setCurrentBlock('container_details_row');
        $tpl->setVariable('TXT_DETAILS', $this->lng->txt('details'));
        $tpl->parseCurrentBlock();
    }
    
    protected function initDetails() : void
    {
        $this->handleSessionExpand();

        if ($session = ilSessionAppointment::lookupNextSessionByCourse($this->getContainerObject()->getRefId())) {
            $this->force_details = $session;
        } elseif ($session = ilSessionAppointment::lookupLastSessionByCourse($this->getContainerObject()->getRefId())) {
            $this->force_details = array($session);
        }
    }

    public static function prepareSessionPresentationLimitation(
        array $items,
        ilContainer $container,
        bool $admin_panel_enabled = false,
        bool $include_side_block = false
    ) : array {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $user = $DIC->user();
        $access = $DIC->access();
        $tree = $DIC->repositoryTree();
        $request = $DIC->container()
            ->internal()
            ->gui()
            ->standardRequest();

        $limit_sessions = false;
        if (
            !$admin_panel_enabled &&
            !$include_side_block &&
            $items['sess'] &&
            is_array($items['sess']) &&
            (($container->getViewMode() == ilContainer::VIEW_SESSIONS) || ($container->getViewMode() == ilContainer::VIEW_INHERIT)) &&
            $container->isSessionLimitEnabled()
        ) {
            $limit_sessions = true;
        }

        if ($container->getViewMode() == ilContainer::VIEW_INHERIT) {
            $parent = $tree->checkForParentType($container->getRefId(), 'crs');
            $crs = ilObjectFactory::getInstanceByRefId($parent, false);
            if (!$crs instanceof ilObjCourse) {
                return $items;
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
            return  $items;
        }



        // do session limit
        if ($request->getPreviousSession() > 0) {
            $user->writePref(
                'crs_sess_show_prev_' . $container->getId(),
                (string) $request->getPreviousSession()
            );
        }
        if ($request->getNextSession() > 0) {
            $user->writePref(
                'crs_sess_show_next_' . $container->getId(),
                (string) $request->getNextSession()
            );
        }

        $session_rbac_checked = [];
        foreach ($items['sess'] as $session_tree_info) {
            if ($access->checkAccess('visible', '', $session_tree_info['ref_id'])) {
                $session_rbac_checked[] = $session_tree_info;
            }
        }
        $sessions = ilArrayUtil::sortArray($session_rbac_checked, 'start', 'ASC', true, false);
        //$sessions = ilUtil::sortArray($this->items['sess'],'start','ASC',true,false);
        $today = new ilDate(date('Ymd', time()), IL_CAL_DATE);
        $previous = $current = $next = array();
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
            $items['sess_link']['prev']['value'] = 1;
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
            $items['sess_link']['next']['value'] = 1;
        }

        $sessions = array_merge($previous, $current, $next);
        $items['sess'] = $sessions;

        // #15389 - see ilContainer::getSubItems()
        $sort = ilContainerSorting::_getInstance($container->getId());
        $items[(int) $admin_panel_enabled][(int) $include_side_block] = $sort->sortItems($items);
        return $items;
    }
}
