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

use Psr\Http\Message\RequestInterface;

/**
 * Taxonomies selection for metadata helper GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilTaxMDGUI: ilFormPropertyDispatchGUI
 */
class ilTaxMDGUI
{
    protected ilObjectDefinition $obj_definition;
    protected ilTree $tree;
    protected int $md_rbac_id;
    protected int $md_obj_id;
    protected string $md_obj_type;
    protected string $requested_post_var;
    protected RequestInterface $request;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected int $ref_id;

    /**
     * Constructor
     */
    public function __construct(
        int $a_md_rbac_id,
        int $a_md_obj_id,
        string $a_md_obj_type,
        int $a_ref_id
    ) {
        global $DIC;

        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();


        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();

        // @todo introduce request wrapper
        $this->request = $DIC->http()->request();

        $this->md_rbac_id = $a_md_rbac_id;
        $this->md_obj_id = $a_md_obj_id;
        $this->md_obj_type = $a_md_obj_type;
        $this->ref_id = $a_ref_id;

        $params = $this->request->getQueryParams();
        $this->requested_post_var = $params["postvar"] ?? "";
    }

    /**
     * Execute command
     * @return mixed|string
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        if ($next_class == 'ilformpropertydispatchgui') {
            $form = $this->initForm();
            $form_prop_dispatch = new ilFormPropertyDispatchGUI();
            $item = $form->getItemByPostVar($this->requested_post_var);
            $form_prop_dispatch->setItem($item);
            return $this->ctrl->forwardCommand($form_prop_dispatch);
        } elseif (in_array($cmd, array("show", "save"))) {
            $this->$cmd();
        }
        return "";
    }

    public function show(): void
    {
        $tpl = $this->tpl;
        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    public function save(): void
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;

        $form = $this->initForm();
        if ($form->checkInput()) {
            $this->updateFromMDForm();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $ctrl->redirect($this, "show");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

    /**
     * Init taxonomy form.
     */
    public function initForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $this->addToMDForm($form);
        $form->addCommandButton("save", $this->lng->txt("save"));
        $form->setTitle($this->lng->txt("tax_tax_assignment"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    // Get selectable taxonomies for current object
    public function getSelectableTaxonomies(): array
    {
        $objDefinition = $this->obj_definition;
        $tree = $this->tree;

        $res = [];
        if ($this->ref_id > 0 && $objDefinition->isRBACObject($this->md_obj_type)) {
            // get all active taxonomies of parent objects
            foreach ($tree->getPathFull($this->ref_id) as $node) {
                // currently only active for categories
                if ((int) $node["ref_id"] != $this->ref_id && $node["type"] == "cat") {
                    if (ilContainer::_lookupContainerSetting(
                        (int) $node["obj_id"],
                        ilObjectServiceSettingsGUI::TAXONOMIES,
                        false
                    ) !== ''
                    ) {
                        $tax_ids = ilObjTaxonomy::getUsageOfObject((int) $node["obj_id"]);
                        if (count($tax_ids) !== 0) {
                            $res = array_merge($res, $tax_ids);
                        }
                    }
                }
            }
        }
        return $res;
    }

    /**
     * @throws ilTaxonomyException
     */
    protected function initTaxNodeAssignment(int $a_tax_id): ilTaxNodeAssignment
    {
        return new ilTaxNodeAssignment($this->md_obj_type, $this->md_obj_id, "obj", $a_tax_id);
    }

    /**
     * Add taxonomy selector to MD (quick edit) form
     */
    public function addToMDForm(ilPropertyFormGUI $a_form): void
    {
        $tax_ids = $this->getSelectableTaxonomies();
        if (is_array($tax_ids)) {
            foreach ($tax_ids as $tax_id) {
                // get existing assignments
                $node_ids = array();
                $ta = $this->initTaxNodeAssignment((int) $tax_id);
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
    public function updateFromMDForm(): void
    {
        $body = $this->request->getParsedBody();
        $tax_ids = $this->getSelectableTaxonomies();
        if (is_array($tax_ids)) {
            foreach ($tax_ids as $tax_id) {
                $ta = $this->initTaxNodeAssignment($tax_id);

                // delete existing assignments
                $ta->deleteAssignmentsOfItem($this->md_obj_id);

                // set current assignment
                if (isset($body["md_tax_" . $tax_id])) {
                    foreach ($body["md_tax_" . $tax_id] as $node_id) {
                        $ta->addAssignment($node_id, $this->md_obj_id);
                    }
                }
            }
        }
    }

    /**
     * addSubTab
     */
    public function addSubTab(): void
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
