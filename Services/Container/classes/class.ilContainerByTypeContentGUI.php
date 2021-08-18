<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Shows all items grouped by type.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerByTypeContentGUI extends ilContainerContentGUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $force_details;

    protected $block_limit;

    /**
     * @var ilContainerUserFilter
     */
    protected $container_user_filter;
    
    /**
    * Constructor
    *
    */
    public function __construct($container_gui_obj, \ilContainerUserFilter $container_user_filter = null)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->user = $DIC->user();
        parent::__construct($container_gui_obj);
        $this->initDetails();
        $this->block_limit = (int) ilContainer::_lookupContainerSetting($container_gui_obj->object->getId(), "block_limit");
        $this->container_user_filter = $container_user_filter;
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
    

    /**
    * Get content HTML for main column.
    */
    public function getMainContent()
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
            $tpl->setVariable("CONTAINER_PAGE_CONTENT", $html);
        } else {	// show item list otherwise
            $html = $this->renderItemList();
            $tpl->setVariable("CONTAINER_PAGE_CONTENT", $html);
        }

        return $tpl->get();
    }
    
    /**
    * Render Items
    */
    public function renderItemList()
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
    
    /**
     * init details
     *
     * @access protected
     * @param
     * @return
     */
    protected function initDetails()
    {
        if (isset($_GET['expand']) && $_GET['expand']) {
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
}
