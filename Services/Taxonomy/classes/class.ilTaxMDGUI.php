<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Taxonomies selection for metadata helper GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilTaxMDGUI: ilFormPropertyDispatchGUI
 * @ingroup ServicesTaxonomy
 */
class ilTaxMDGUI
{
    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilTree
     */
    protected $tree;

    protected $md_rbac_id; // [int]
    protected $md_obj_id; // [int]
    protected $md_obj_type; // [string]


    /**
     * Constructor
     *
     * @param int $a_md_rbac_id
     * @param int $a_md_obj_id
     * @param int $a_md_obj_type
     * @return self
     */
    public function __construct($a_md_rbac_id, $a_md_obj_id, $a_md_obj_type, $a_ref_id)
    {
        global $DIC;

        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();


        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->md_rbac_id = $a_md_rbac_id;
        $this->md_obj_id = $a_md_obj_id;
        $this->md_obj_type = $a_md_obj_type;
        $this->ref_id = $a_ref_id;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        switch ($next_class) {
            case 'ilformpropertydispatchgui':
                $form = $this->initForm();
                include_once './Services/Form/classes/class.ilFormPropertyDispatchGUI.php';
                $form_prop_dispatch = new ilFormPropertyDispatchGUI();
                $item = $form->getItemByPostVar($_GET["postvar"]);
                $form_prop_dispatch->setItem($item);
                return $this->ctrl->forwardCommand($form_prop_dispatch);

            default:
                if (in_array($cmd, array("show", "save"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Show
     *
     * @param
     * @return
     */
    public function show()
    {
        $tpl = $this->tpl;

        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Save form
     */
    public function save()
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;

        $form = $this->initForm();
        if ($form->checkInput()) {
            $this->updateFromMDForm();
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $ctrl->redirect($this, "show");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }

    /**
     * Init taxonomy form.
     */
    public function initForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $this->addToMDForm($form);

        $form->addCommandButton("save", $this->lng->txt("save"));


        $form->setTitle($this->lng->txt("tax_tax_assignment"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Get selectable taxonomies for current object
     *
     * @return array
     */
    public function getSelectableTaxonomies()
    {
        $objDefinition = $this->obj_definition;
        $tree = $this->tree;
        
        if ($this->ref_id > 0 && $objDefinition->isRBACObject($this->md_obj_type)) {
            $res = array();
            
            // see ilTaxonomyBlockGUI::getActiveTaxonomies()
                        
            // get all active taxonomies of parent objects
            foreach ($tree->getPathFull((int) $this->ref_id) as $node) {
                if ($node["ref_id"] != (int) $this->ref_id) {
                    // currently only active for categories
                    if ($node["type"] == "cat") {
                        include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
                        include_once "Services/Container/classes/class.ilContainer.php";
                        if (ilContainer::_lookupContainerSetting(
                            $node["obj_id"],
                            ilObjectServiceSettingsGUI::TAXONOMIES,
                            false
                        )
                        ) {
                            include_once "Services/Taxonomy/classes/class.ilObjTaxonomy.php";
                            $tax_ids = ilObjTaxonomy::getUsageOfObject($node["obj_id"]);
                            if (sizeof($tax_ids)) {
                                $res = array_merge($res, $tax_ids);
                            }
                        }
                    }
                }
            }
            
            if (sizeof($res)) {
                return $res;
            }
        }
    }
    
    /**
     * Init tax node assignment
     *
     * @param int $a_tax_id
     * @return ilTaxNodeAssignment
     */
    protected function initTaxNodeAssignment($a_tax_id)
    {
        include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
        return new ilTaxNodeAssignment($this->md_obj_type, $this->md_obj_id, "obj", $a_tax_id);
    }
    
    /**
     * Add taxonomy selector to MD (quick edit) form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function addToMDForm(ilPropertyFormGUI $a_form)
    {
        $tax_ids = $this->getSelectableTaxonomies();
        if (is_array($tax_ids)) {
            include_once "Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php";
            foreach ($tax_ids as $tax_id) {
                // get existing assignments
                $node_ids = array();
                $ta = $this->initTaxNodeAssignment($tax_id);
                foreach ($ta->getAssignmentsOfItem($this->md_obj_id) as $ass) {
                    $node_ids[] = $ass["node_id"];
                }
                
                $tax_sel = new ilTaxSelectInputGUI($tax_id, "md_tax_" . $tax_id, true);
                $tax_sel->setValue($node_ids);
                $a_form->addItem($tax_sel);
            }
        }
    }
    
    /**
     * Import settings from MD (quick edit) form
     */
    public function updateFromMDForm()
    {
        $tax_ids = $this->getSelectableTaxonomies();
        if (is_array($tax_ids)) {
            include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
            
            foreach ($tax_ids as $tax_id) {
                $ta = $this->initTaxNodeAssignment($tax_id);
                
                // delete existing assignments
                $ta->deleteAssignmentsOfItem($this->md_obj_id);
                            
                // set current assignment
                if (is_array($_POST["md_tax_" . $tax_id])) {
                    foreach ($_POST["md_tax_" . $tax_id] as $node_id) {
                        $ta->addAssignment($node_id, $this->md_obj_id);
                    }
                }
            }
        }
    }

    /**
     * addSubTab
     *
     * @param
     * @return
     */
    public function addSubTab()
    {
        $tabs = $this->tabs;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $tax_ids = $this->getSelectableTaxonomies();
        if (is_array($tax_ids)) {
            $tabs->addSubTab(
                "tax_assignment",
                $lng->txt("tax_tax_assignment"),
                $ctrl->getLinkTarget($this, "")
            );
        }
    }
}
