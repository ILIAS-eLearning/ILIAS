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
 * Shows all items grouped by type.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerByTypeContentGUI extends ilContainerContentGUI
{
    protected bool $force_details;
    protected int $block_limit;
    protected ?ilContainerUserFilter $container_user_filter;
    
    public function __construct(
        ilContainerGUI $container_gui_obj,
        ilContainerUserFilter $container_user_filter = null
    ) {
        global $DIC;

        $this->access = $DIC->access();
        $this->user = $DIC->user();
        parent::__construct($container_gui_obj);
        $this->initDetails();
        $this->block_limit = (int) ilContainer::_lookupContainerSetting($container_gui_obj->getObject()->getId(), "block_limit");
        $this->container_user_filter = $container_user_filter;
    }
    
    protected function getDetailsLevel(int $a_item_id) : int
    {
        if ($this->getContainerGUI()->isActiveAdministrationPanel()) {
            return self::DETAILS_DEACTIVATED;
        }
        if ($this->item_manager->getExpanded($a_item_id) !== null) {
            return $this->item_manager->getExpanded($a_item_id);
        }
        if ($a_item_id == $this->force_details) {
            return self::DETAILS_ALL;
        } else {
            return self::DETAILS_TITLE;
        }
    }

    public function getMainContent() : string
    {
        $ilAccess = $this->access;

        $tpl = new ilTemplate(
            "tpl.container_page.html",
            true,
            true,
            "Services/Container"
        );
        
        // get all sub items
        $this->items = $this->getContainerObject()->getSubItems(
            $this->getContainerGUI()->isActiveAdministrationPanel(),
            false,
            0,
            $this->container_user_filter
        );

        //$this->items = $this->applyFilterToItems($this->items);

        // Show introduction, if repository is empty
        // @todo: maybe we move this
        if ((!is_array($this->items) || count($this->items) == 0) &&
            $this->getContainerObject()->getRefId() == ROOT_FOLDER_ID &&
            $ilAccess->checkAccess("write", "", $this->getContainerObject()->getRefId())) {
            $html = $this->getIntroduction();
        } else {	// show item list otherwise
            $html = $this->renderItemList();
        }
        $tpl->setVariable("CONTAINER_PAGE_CONTENT", $html);

        return $tpl->get();
    }
    
    public function renderItemList() : string
    {
        $this->clearAdminCommandsDetermination();
    
        $this->initRenderer();
        
        // text/media page content
        $output_html = $this->getContainerGUI()->getContainerPageHTML();
        
        // get embedded blocks
        if ($output_html != "") {
            $output_html = $this->insertPageEmbeddedBlocks($output_html);
        }

        // item groups
        $pos = $this->getItemGroupsHTML();
        
        // iterate all types
        foreach ($this->getGroupedObjTypes() as $type => $v) {
            if (isset($this->items[$type]) && is_array($this->items[$type]) &&
                $this->renderer->addTypeBlock($type)) {
                $this->renderer->setBlockPosition($type, ++$pos);
                
                $position = 1;
                
                foreach ($this->items[$type] as $item_data) {
                    $item_ref_id = $item_data["child"];

                    if ($this->block_limit > 0 && !$this->getContainerGUI()->isActiveItemOrdering() && $position == $this->block_limit + 1) {
                        if ($position == $this->block_limit + 1) {
                            // render more button
                            $this->renderer->addShowMoreButton($type);
                        }
                        continue;
                    }

                    if (!$this->renderer->hasItem($item_ref_id)) {
                        $html = $this->renderItem($item_data, $position++);
                        if ($html != "") {
                            $this->renderer->addItemToBlock($type, $item_data["type"], $item_ref_id, $html);
                        }
                    }
                }
            }
        }
        
        $output_html .= $this->renderer->getHTML();
        
        return $output_html;
    }
    
    protected function initDetails() : void
    {
        $this->handleSessionExpand();

        if ($this->getContainerObject()->getType() == 'crs') {
            if ($session = ilSessionAppointment::lookupNextSessionByCourse($this->getContainerObject()->getRefId())) {
                $this->force_details = $session;
            } elseif ($session = ilSessionAppointment::lookupLastSessionByCourse($this->getContainerObject()->getRefId())) {
                $this->force_details = $session;
            }
        }
    }
}
