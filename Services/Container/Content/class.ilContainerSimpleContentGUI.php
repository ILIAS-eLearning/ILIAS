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
class ilContainerSimpleContentGUI extends ilContainerContentGUI
{
    protected ilTabsGUI $tabs;
    protected int $force_details;
    
    public function __construct(
        ilContainerGUI $container_gui_obj
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        parent::__construct($container_gui_obj);
        $this->initDetails();
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

        // Feedback
        // @todo
        //		$this->__showFeedBack();

        $this->__showMaterials($tpl);
            
        return $tpl->get();
    }

    public function __showMaterials(
        ilTemplate $a_tpl
    ) : void {
        $lng = $this->lng;

        $this->items = $this->getContainerObject()->getSubItems($this->getContainerGUI()->isActiveAdministrationPanel());
        $this->clearAdminCommandsDetermination();
        
        $this->initRenderer();
        
        $output_html = $this->getContainerGUI()->getContainerPageHTML();
        
        // get embedded blocks
        if ($output_html != "") {
            $output_html = $this->insertPageEmbeddedBlocks($output_html);
        }

        // item groups
        $this->getItemGroupsHTML();
        
        if (is_array($this->items["_all"])) {
            $title = $this->getContainerObject()->filteredSubtree()
                ? $lng->txt("cont_found_objects")
                : $lng->txt("content");
            $this->renderer->addCustomBlock("_all", $title);
            
            $position = 1;
            foreach ($this->items["_all"] as $k => $item_data) {
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
}
