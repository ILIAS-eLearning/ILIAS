<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Shows all items in one block.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilContainerSimpleContentGUI extends ilContainerContentGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    protected $force_details;
    
    /**
    * Constructor
    *
    */
    public function __construct($container_gui_obj)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        parent::__construct($container_gui_obj);
        $this->initDetails();
    }


    /**
    * Get content HTML for main column.
    */
    public function getMainContent()
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

    /**
    * Show Materials
    */
    public function __showMaterials($a_tpl)
    {
        $ilAccess = $this->access;
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
    
    /**
     * init details
     *
     * @access protected
     * @param
     * @return
     */
    protected function initDetails()
    {
        $ilUser = $this->user;
        
        if ($_GET['expand']) {
            if ($_GET['expand'] > 0) {
                $_SESSION['sess']['expanded'][abs((int) $_GET['expand'])] = self::DETAILS_ALL;
            } else {
                $_SESSION['sess']['expanded'][abs((int) $_GET['expand'])] = self::DETAILS_TITLE;
            }
        }
        
        
        if ($this->getContainerObject()->getType() == 'crs') {
            if ($session = ilSessionAppointment::lookupNextSessionByCourse($this->getContainerObject()->getRefId())) {
                $this->force_details = $session;
            } elseif ($session = ilSessionAppointment::lookupLastSessionByCourse($this->getContainerObject()->getRefId())) {
                $this->force_details = $session;
            }
        }
    }
    
    /**
     * get details level
     *
     * @access public
     * @param	int	$a_session_id
     * @return	int	DEATAILS_LEVEL
     */
    public function getDetailsLevel($a_session_id)
    {
        if ($this->getContainerGUI()->isActiveAdministrationPanel()) {
            return self::DETAILS_DEACTIVATED;
        }
        if (isset($_SESSION['sess']['expanded'][$a_session_id])) {
            return $_SESSION['sess']['expanded'][$a_session_id];
        }
        if ($a_session_id == $this->force_details) {
            return self::DETAILS_ALL;
        } else {
            return self::DETAILS_TITLE;
        }
    }
} // END class.ilContainerSimpleContentGUI
