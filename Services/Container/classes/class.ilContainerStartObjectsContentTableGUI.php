<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * ilContainerStartObjectsContentTableGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesContainer
 */
class ilContainerStartObjectsContentTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    protected $start_object; // [ilContainerStartObjects]
    protected $item_list_guis; // [array]
    protected $enable_desktop; // [bool]
    
    public function __construct($a_parent_obj, $a_parent_cmd, ilContainerStartObjects $a_start_objects, $a_enable_desktop = true)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->access = $DIC->access();
        $this->obj_definition = $DIC["objDefinition"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->start_object = $a_start_objects;
        $this->enable_desktop = (bool) $a_enable_desktop;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
            
        $this->addColumn($this->lng->txt('crs_nr'), 'nr');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('crs_objective_accomplished'), 'status');
        $this->addColumn($this->lng->txt('actions'), '');
        
        $this->setTitle($this->lng->txt('crs_table_start_objects'));
        $this->setDescription($this->lng->txt('crs_info_start'));
     
        $this->setRowTemplate("tpl.start_objects_content_row.html", "Services/Container");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        
        $this->setDefaultOrderField('nr');
        $this->setDefaultOrderDirection('asc');
        
        $this->getItems();
    }
    
    protected function getItems()
    {
        $ilUser = $this->user;
        $ilObjDataCache = $this->obj_data_cache;
        $ilAccess = $this->access;
        
        include_once './Modules/Course/classes/class.ilCourseLMHistory.php';
        include_once './Services/Link/classes/class.ilLink.php';
        
        $lm_continue = new ilCourseLMHistory($this->start_object->getRefId(), $ilUser->getId());
        $continue_data = $lm_continue->getLMHistory();
    
        $items = array();
        $counter = 0;
        foreach ($this->start_object->getStartObjects() as $start) {
            $obj_id = $ilObjDataCache->lookupObjId($start['item_ref_id']);
            $ref_id = $start['item_ref_id'];
            $type = $ilObjDataCache->lookupType($obj_id);
            
            if (!$ilAccess->checkAccess("visible", "", $ref_id)) {
                continue;
            }
        
            // start object status
            if ($this->start_object->isFullfilled($ilUser->getId(), $ref_id)) {
                $accomplished = 'accomplished';
            } else {
                $accomplished = 'not_accomplished';
            }
            
            // add/remove desktop
            $actions = array();
            if ((bool) $this->enable_desktop) {
                // add to desktop link
                if (!$ilUser->isDesktopItem($ref_id, $type)) {
                    if ($ilAccess->checkAccess('read', '', $ref_id)) {
                        $this->ctrl->setParameter($this->getParentObject(), 'item_ref_id', $ref_id);
                        $this->ctrl->setParameter($this->getParentObject(), 'item_id', $ref_id);
                        $this->ctrl->setParameter($this->getParentObject(), 'type', $type);
                        $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'addToDesk');
                        $actions[$url] = $this->lng->txt("to_desktop");
                    }
                } else {
                    $this->ctrl->setParameter($this->getParentObject(), 'item_ref_id', $ref_id);
                    $this->ctrl->setParameter($this->getParentObject(), 'item_id', $ref_id);
                    $this->ctrl->setParameter($this->getParentObject(), 'type', $type);
                    $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'removeFromDesk');
                    $actions[$url] = $this->lng->txt("unsubscribe");
                }
            }
            
            $default_params = null;
            if ($type == "tst") {
                $default_params["crs_show_result"] = $ref_id;
            }
            /* continue is currently inactive
            if(isset($continue_data[$ref_id]))
            {
                // :TODO: should "continue" be default or 2nd link/action?
                // $this->lng->txt('continue_work')
                $default_params["obj_id"] = $continue_data[$ref_id]['lm_page_id'];
            }
            */

            if ($accomplished == 'accomplished') {
                $icon = ilUtil::getImagePath("icon_ok.svg");
            } else {
                $icon = ilUtil::getImagePath("icon_not_ok.svg");
            }
            
            $items[] = array("nr" => ++$counter,
                "obj_id" => $obj_id,
                "ref_id" => $ref_id,
                "type" => $type,
                "append_default" => $default_params,
                "title" => $ilObjDataCache->lookupTitle($obj_id),
                "description" => $ilObjDataCache->lookupDescription($obj_id),
                "status" => $this->lng->txt('crs_objective_' . $accomplished),
                "status_img" => $icon,
                "actions" => $actions);
        }
        
        include_once("./Services/Object/classes/class.ilObjectListGUIPreloader.php");
        $preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_REPOSITORY);
        foreach ($items as $item) {
            $preloader->addItem($item["obj_id"], $item["type"], $item["ref_id"]);
        }
        $preloader->preload();
        unset($preloader);

        reset($items);
        $this->setData($items);
    }
        
    /**
     * Get list gui for object type
     *
     * @see ilPDSelectedItemsBlockGUI
     *
     * @param string $a_type
     * @return ilObjectListGUI
     */
    protected function getItemListGUI($a_type)
    {
        $objDefinition = $this->obj_definition;

        if (!isset($this->item_list_guis[$a_type])) {
            $class = $objDefinition->getClassName($a_type);
            // Fixed problem with deactivated plugins and existing repo. object plugin objects on the user's desktop
            if (!$class) {
                return null;
            }
            // Fixed problem with deactivated plugins and existing repo. object plugin objects on the user's desktop
            $location = $objDefinition->getLocation($a_type);
            if (!$location) {
                return null;
            }
            $full_class = "ilObj" . $class . "ListGUI";
            include_once($location . "/class." . $full_class . ".php");
            $item_list_gui = new $full_class();
            $this->item_list_guis[$a_type] = $item_list_gui;
        } else {
            $item_list_gui = $this->item_list_guis[$a_type];
        }
        
        $item_list_gui->setDefaultCommandParameters(array());
        
        return $item_list_gui;
    }
    
    /**
     * Get list gui for object instance
     *
     * @param array $a_item
     * @return string
     */
    protected function getListItem($a_item)
    {
        $item_list_gui = $this->getItemListGUI($a_item["type"]);
        if (!$item_list_gui) {
            return;
        }

        $item_list_gui->setContainerObject($this);
        $item_list_gui->enableCommands(true, true);
        
        // ilObjectActivation::addListGUIActivationProperty($item_list_gui, $a_item);

        // notes, comment currently do not work properly
        $item_list_gui->enableNotes(false);
        $item_list_gui->enableComments(false);
        $item_list_gui->enableTags(false);

        $item_list_gui->enableIcon(true);
        $item_list_gui->enableDelete(false);
        $item_list_gui->enableCut(false);
        $item_list_gui->enableCopy(false);
        $item_list_gui->enableLink(false);
        $item_list_gui->enableInfoScreen(true);
        $item_list_gui->enableSubscribe(false);
        
        $level = 3;
        
        if ($level < 3) {
            $item_list_gui->enableDescription(false);
            $item_list_gui->enableProperties(false);
            $item_list_gui->enablePreconditions(false);
        }
        
        if ($a_item["append_default"]) {
            $item_list_gui->setDefaultCommandParameters($a_item["append_default"]);
        }
        if (is_object($item_list_gui)) {
            return $item_list_gui->getListItemHTML(
                $a_item["ref_id"],
                $a_item["obj_id"],
                $a_item["title"],
                $a_item["description"]
            );
        }
    }
    
    public function fillRow($a_set)
    {
        $this->tpl->setVariable("VAL_NR", $a_set["nr"]);
        
        // begin-patch lok
        $this->tpl->setVariable("TXT_TITLE", $this->getListItem($a_set));
        /*
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        if(ilLOSettings::getInstanceByObjId($this->getParentObject()->object->getId())->isObjectiveTest($a_set['ref_id']))
        {
            $this->ctrl->setParameter($this->getParentObject(),'tid',$a_set['ref_id']);
            $this->tpl->setVariable('TYPE_IMG',ilUtil::getTypeIconPath($a_set['type'], $a_set['obj_id'], 'small'));
            $this->tpl->setVariable('TITLE_MANUAL_LINK',$this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()),'redirectLocToTest'));
            $this->tpl->setVariable('VAL_TITLE_MANUAL',$a_set['title']);
        }
        else
        {
            $this->tpl->setVariable('TYPE_IMG',ilUtil::getTypeIconPath($a_set['type'], $a_set['obj_id'], 'small'));
            include_once './Services/Link/classes/class.ilLink.php';
            $this->tpl->setVariable('TITLE_MANUAL_LINK',ilLink::_getLink($a_set['ref_id']));
            $this->tpl->setVariable('VAL_TITLE_MANUAL',$a_set['title']);
        }
        // end-patch lok
        */
        
        $this->tpl->setVariable("TXT_STATUS", $a_set["status"]);
        $this->tpl->setVariable("IMG_STATUS", $a_set["status_img"]);
        
        if ($a_set["actions"]) {
            $this->tpl->setCurrentBlock("link");
            foreach ($a_set["actions"] as $url => $caption) {
                $this->tpl->setVariable("LINK_HREF", $url);
                $this->tpl->setVariable("LINK_NAME", $caption);
            }
            $this->tpl->parseCurrentBlock();
        }
    }
}
