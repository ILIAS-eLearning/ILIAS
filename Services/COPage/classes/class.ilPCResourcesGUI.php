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
 * User Interface for Resources Component Editing
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCResourcesGUI extends ilPageContentGUI
{
    protected ilTree $rep_tree;
    protected ilObjectDefinition $obj_definition;


    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->obj_definition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();
        
        $this->rep_tree = $tree;
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand() : void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function insert() : void
    {
        $this->edit(true);
    }

    public function edit(bool $a_insert = false) : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;

        $op_type = null;
        $op_itemgroup = null;
        
        $this->displayValidationError();
        
        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_resources"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_resources"));
        }
        
        // count number of existing objects per type and collect item groups
        $ref_id = $this->requested_ref_id;
        $childs = $this->rep_tree->getChilds($ref_id);
        $type_counts = array();
        $item_groups = array();
        foreach ($childs as $c) {
            // see bug #12471
            //echo "<br>-".$c["type"]."-".$objDefinition->getGroupOfObj($c["type"])."-";
            $key = ($objDefinition->getGroupOfObj($c["type"]) != "")
                ? $objDefinition->getGroupOfObj($c["type"])
                : $c["type"];
            $type_counts[$key] = ($type_counts[$key] ?? 0) + 1;
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
        $obj_id = ilObject::_lookupObjId($this->requested_ref_id);
        $obj_type = ilObject::_lookupType($obj_id);
        $sub_objs = $objDefinition->getGroupedRepositoryObjectTypes($obj_type);
        $types = array();
        foreach ($sub_objs as $k => $so) {
            if (!$objDefinition->isPlugin($k)) {
                if ($k != "itgr") {
                    $types[$k] = $this->lng->txt("objs_" . $k) . " (" . (int) ($type_counts[$k] ?? 0) . ")";
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
    }

    public function create() : void
    {
        $this->content_obj = new ilPCResources($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);

        if ($this->request->getString("res_type") != "itgr") {
            $this->content_obj->setResourceListType(
                $this->request->getString("type")
            );
        } else {
            $this->content_obj->setItemGroupRefId(
                $this->request->getString("itgr")
            );
        }
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }

    public function update() : void
    {
        if ($this->request->getString("res_type") != "itgr") {
            $this->content_obj->setResourceListType(
                $this->request->getString("type")
            );
        } else {
            $this->content_obj->setItemGroupRefId(
                $this->request->getString("itgr")
            );
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
     */
    public static function insertResourcesIntoPageContent(
        string $a_content
    ) : string {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();
        $lng = $DIC->language();

        $ref_id = $DIC
            ->copage()
            ->internal()
            ->gui()
            ->pc()
            ->editRequest()
            ->getRefId();
        $obj_id = ilObject::_lookupObjId($ref_id);
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
            if (isset($type_to_grp[$child["type"]])) {
                $childs_by_type[$type_to_grp[$child["type"]]][] = $child;
                if ($child["type"] == "itgr") {
                    $item_groups[(int) $child["ref_id"]] = $child["title"];
                }
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
                        $tpl->setVariable("IMG", ilUtil::img(ilObject::_getIcon((int) $child["obj_id"], "small")));
                        $tpl->setVariable("TITLE", $child["title"]);
                        $tpl->parseCurrentBlock();
                        $cnt++;
                    }
                } else {
                    $tpl->setCurrentBlock("row");
                    $tpl->setVariable("TITLE", $lng->txt("no_items"));
                    $tpl->parseCurrentBlock();
                }
                $tpl->setVariable("HEADER", $lng->txt("objs_" . $type));
                $a_content = str_replace("[list-" . $type . "]", $tpl->get(), $a_content);
            }
        }
        
        // handle item groups
        while (preg_match('/\[(item-group-([0-9]*))\]/i', $a_content, $found)) {
            $itgr_ref_id = (int) $found[2];
            
            // check whether this item group is child -> insert editing html
            if (isset($item_groups[$itgr_ref_id])) {
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
