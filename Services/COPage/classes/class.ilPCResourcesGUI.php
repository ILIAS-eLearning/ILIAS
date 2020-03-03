<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCResources.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCResourcesGUI
*
* User Interface for Resources Component Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCResourcesGUI extends ilPageContentGUI
{
    /**
     * @var ilTree
     */
    protected $rep_tree;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;


    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->obj_definition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();
        
        $this->rep_tree = $tree;
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
    * Insert new resources component form.
    */
    public function insert()
    {
        $this->edit(true);
    }

    /**
    * Edit resources form.
    */
    public function edit($a_insert = false)
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;
        
        $this->displayValidationError();
        
        // edit form
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_resources"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_resources"));
        }
        
        // count number of existing objects per type and collect item groups
        $ref_id = (int) $_GET["ref_id"];
        $childs = $this->rep_tree->getChilds($ref_id);
        $type_counts = array();
        $item_groups = array();
        foreach ($childs as $c) {
            // see bug #12471
            //echo "<br>-".$c["type"]."-".$objDefinition->getGroupOfObj($c["type"])."-";
            $key = ($objDefinition->getGroupOfObj($c["type"]) != "")
                ? $objDefinition->getGroupOfObj($c["type"])
                : $c["type"];
            $type_counts[$key] += 1;
            if ($c["type"] == "itgr") {
                $item_groups[$c["ref_id"]] = $c["title"];
            }
        }
        
        if (count($item_groups) > 0) {
            // radio group for type selection
            $radg = new ilRadioGroupInputGUI($lng->txt("cont_resources"), "res_type");
            if (!$a_insert && $this->content_obj->getMainType() == "ItemGroup") {
                $radg->setValue("itgr");
            } else {
                $radg->setValue("by_type");
            }
            
            $op_type = new ilRadioOption($lng->txt("cont_resources_of_type"), "by_type", "");
            $radg->addOption($op_type);
            $op_itemgroup = new ilRadioOption($lng->txt("obj_itgr"), "itgr", "");
            $radg->addOption($op_itemgroup);
            $form->addItem($radg);
        }
        
        // type selection
        $type_prop = new ilSelectInputGUI(
            $this->lng->txt("cont_type"),
            "type"
        );
        $obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
        $obj_type = ilObject::_lookupType($obj_id);
        $sub_objs = $objDefinition->getGroupedRepositoryObjectTypes($obj_type);
        $types = array();
        foreach ($sub_objs as $k => $so) {
            if (!$objDefinition->isPlugin($k)) {
                if ($k != "itgr") {
                    $types[$k] = $this->lng->txt("objs_" . $k) . " (" . (int) $type_counts[$k] . ")";
                }
            } else {
                $pl = ilObjectPlugin::getPluginObjectByType($k);
                $types[$k] = $pl->txt("objs_" . $k) . " (" . (int) $type_counts[$k] . ")";
            }

        }
        $type_prop->setOptions($types);
        $selected = ($a_insert)
            ? ""
            : $this->content_obj->getResourceListType();
        $type_prop->setValue($selected);
        if (count($item_groups) > 0) {
            $op_type->addSubItem($type_prop);
        } else {
            $form->addItem($type_prop);
        }
        
        if (count($item_groups) > 0) {
            // item groups
            $options = $item_groups;
            $si = new ilSelectInputGUI($this->lng->txt("obj_itgr"), "itgr");
            $si->setOptions($options);
            $selected = ($a_insert)
                ? ""
                : $this->content_obj->getItemGroupRefId();
            $op_itemgroup->addSubItem($si);
        }
            
        
        // save/cancel buttons
        if ($a_insert) {
            $form->addCommandButton("create_resources", $lng->txt("save"));
            $form->addCommandButton("cancelCreate", $lng->txt("cancel"));
        } else {
            $form->addCommandButton("update_resources", $lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
        }
        $html = $form->getHTML();
        $tpl->setContent($html);
        return $ret;
    }


    /**
    * Create new Resources Component.
    */
    public function create()
    {
        $this->content_obj = new ilPCResources($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        
        if ($_POST["res_type"] != "itgr") {
            $this->content_obj->setResourceListType(ilUtil::stripSlashes($_POST["type"]));
        } else {
            $this->content_obj->setItemGroupRefId(ilUtil::stripSlashes($_POST["itgr"]));
        }
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }

    /**
    * Update Resources Component.
    */
    public function update()
    {
        if ($_POST["res_type"] != "itgr") {
            $this->content_obj->setResourceListType(ilUtil::stripSlashes($_POST["type"]));
        } else {
            $this->content_obj->setItemGroupRefId(ilUtil::stripSlashes($_POST["itgr"]));
        }
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }
    
    /**
     * Insert resources (see also ilContainerContentGUI::determinePageEmbeddedBlocks for presentation)
     *
     * @param
     * @return
     */
    public static function insertResourcesIntoPageContent($a_content)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();
        $lng = $DIC->language();

        $ref_id = (int) $_GET["ref_id"];
        $obj_id = (int) ilObject::_lookupObjId($ref_id);
        $obj_type = ilObject::_lookupType($obj_id);
        
        // determine type -> group
        $type_to_grp = array();
        $type_grps =
            $objDefinition->getGroupedRepositoryObjectTypes($obj_type);
        foreach ($type_grps as $grp => $def) {
            foreach ($def["objs"] as $t) {
                $type_to_grp[$t] = $grp;
            }
        }

        $childs = $tree->getChilds($ref_id);
        $childs_by_type = array();
        $item_groups = array();
        foreach ($childs as $child) {
            $childs_by_type[$type_to_grp[$child["type"]]][] = $child;
            if ($child["type"] == "itgr") {
                $item_groups[(int) $child["ref_id"]] = $child["title"];
            }
        }

        // handle "by type" lists
        foreach ($type_grps as $type => $v) {
            if (is_int(strpos($a_content, "[list-" . $type . "]"))) {
                // render block
                $tpl = new ilTemplate("tpl.resource_block.html", true, true, "Services/COPage");
                $cnt = 0;
                
                if (is_array($childs_by_type[$type]) && count($childs_by_type[$type]) > 0) {
                    foreach ($childs_by_type[$type] as $child) {
                        $tpl->setCurrentBlock("row");
                        $tpl->setVariable("IMG", ilUtil::img(ilObject::_getIcon($child["obj_id"], "small")));
                        $tpl->setVariable("TITLE", $child["title"]);
                        $tpl->parseCurrentBlock();
                        $cnt++;
                    }
                    $tpl->setVariable("HEADER", $lng->txt("objs_" . $type));
                    $a_content = str_replace("[list-" . $type . "]", $tpl->get(), $a_content);
                } else {
                    $tpl->setCurrentBlock("row");
                    $tpl->setVariable("TITLE", $lng->txt("no_items"));
                    $tpl->parseCurrentBlock();
                    $tpl->setVariable("HEADER", $lng->txt("objs_" . $type));
                    $a_content = str_replace("[list-" . $type . "]", $tpl->get(), $a_content);
                }
            }
        }
        
        // handle item groups
        while (preg_match('/\[(item-group-([0-9]*))\]/i', $a_content, $found)) {
            $itgr_ref_id = (int) $found[2];
            
            // check whether this item group is child -> insert editing html
            if (isset($item_groups[$itgr_ref_id])) {
                include_once("./Modules/ItemGroup/classes/class.ilItemGroupItems.php");
                $itgr_items = new ilItemGroupItems($itgr_ref_id);
                $items = $itgr_items->getValidItems();
                
                // render block
                $tpl = new ilTemplate("tpl.resource_block.html", true, true, "Services/COPage");
                foreach ($items as $it_ref_id) {
                    $it_obj_id = ilObject::_lookupObjId($it_ref_id);
                    $it_title = ilObject::_lookupTitle($it_obj_id);
                    $it_type = ilObject::_lookupType($it_obj_id);

                    // TODO: Handle this switch by module.xml definitions
                    if (in_array($it_type, array("catr", "crsr", "grpr"))) {
                        include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
                        $it_title = ilContainerReference::_lookupTitle($it_obj_id);
                    }


                    $tpl->setCurrentBlock("row");
                    $tpl->setVariable("IMG", ilUtil::img(ilObject::_getIcon($it_obj_id, "small")));
                    $tpl->setVariable("TITLE", $it_title);
                    $tpl->parseCurrentBlock();
                }
                $tpl->setVariable("HEADER", $item_groups[$itgr_ref_id]);
                $html = $tpl->get();
            } else {
                $html = "<i>" . $lng->txt("cont_element_refers_removed_itgr") . "</i>";
            }
            $a_content = preg_replace('/\[' . $found[1] . '\]/i', $html, $a_content);
        }
        

        return $a_content;
    }
}
