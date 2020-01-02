<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclDetailedViewDefinitionGUI
 *
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Marcel Raimann <mr@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilDclDetailedViewDefinitionGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilDclDetailedViewDefinitionGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 */
class ilDclDetailedViewDefinitionGUI extends ilPageObjectGUI
{

    /**
     * @var ilDclDetailedViewDefinition
     */
    public $obj;
    /**
     * @var ilCtrl
     */
    public $ctrl;


    /**
     * @param     $table_id
     * @param int $a_definition_id
     */
    public function __construct($tableview_id, $a_definition_id = 0)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        /**
         * @var $ilCtrl ilCtrl
         */
        $this->ctrl = $ilCtrl;
        $this->tableview_id = $tableview_id;
        //TODO Permission-Check

        // we always need a page object - create on demand
        if (!ilPageObject::_exists('dclf', $tableview_id)) {
            $viewdef = new ilDclDetailedViewDefinition();
            $viewdef->setId($tableview_id);
            $viewdef->setParentId(ilObject2::_lookupObjectId($_GET['ref_id']));
            $viewdef->setActive(false);
            $viewdef->create();
        }

        parent::__construct("dclf", $tableview_id);

        // content style (using system defaults)
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");

        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $tpl->parseCurrentBlock();
    }


    /**
     * execute command
     */
    public function executeCommand()
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];
        $lng = $DIC['lng'];

        $next_class = $this->ctrl->getNextClass($this);

        $viewdef = $this->getPageObject();
        if ($viewdef) {
            $this->ctrl->setParameter($this, "dclv", $viewdef->getId());
            $title = $lng->txt("dcl_view_viewdefinition");
        }

        switch ($next_class) {
            case "ilpageobjectgui":
                throw new ilCOPageException("Deprecated. ilDclDetailedViewDefinitionGUI gui forwarding to ilpageobject");
            default:
                if ($viewdef) {
                    $this->setPresentationTitle($title);
                    $ilLocator->addItem($title, $this->ctrl->getLinkTarget($this, "preview"));
                }

                return parent::executeCommand();
        }
    }


    public function showPage()
    {
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];
        /**
         * @var $ilToolbar ilToolbarGUI
         */
        // :TODO: temporary legend of available placeholders
        if ($this->getOutputMode() == IL_PAGE_EDIT) {
            $delete_button = ilLinkButton::getInstance();
            $delete_button->setCaption('dcl_empty_detailed_view');
            $delete_button->setUrl($this->ctrl->getLinkTarget($this, 'confirmDelete'));
            $ilToolbar->addButtonInstance($delete_button);

            $activation_button = ilLinkButton::getInstance();
            if ($this->getPageObject()->getActive()) {
                $activation_button->setCaption('dcl_deactivate_view');
                $activation_button->setUrl($this->ctrl->getLinkTarget($this, 'deactivate'));
            } else {
                $activation_button->setCaption('dcl_activate_view');
                $activation_button->setUrl($this->ctrl->getLinkTarget($this, 'activate'));
            }

            $ilToolbar->addButtonInstance($activation_button);

            $legend = $this->getPageObject()->getAvailablePlaceholders();
            if (sizeof($legend)) {
                $this->setPrependingHtml(
                    "<span class=\"small\">" . $this->lng->txt("dcl_legend_placeholders") . ": " . implode(" ", $legend)
                    . "</span>"
                );
            }
        }

        return parent::showPage();
    }


    protected function activate()
    {
        $page = $this->getPageObject();
        $page->setActive(true);
        $page->update();
        $this->ctrl->redirect($this, 'edit');
    }


    protected function deactivate()
    {
        $page = $this->getPageObject();
        $page->setActive(false);
        $page->update();
        $this->ctrl->redirect($this, 'edit');
    }


    /**
     * confirmDelete
     */
    public function confirmDelete()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this));
        $conf->setHeaderText($lng->txt('dcl_confirm_delete_detailed_view_title'));

        $conf->addItem('tableview', (int) $this->tableview_id, $lng->txt('dcl_confirm_delete_detailed_view_text'));

        $conf->setConfirm($lng->txt('delete'), 'deleteView');
        $conf->setCancel($lng->txt('cancel'), 'cancelDelete');

        $tpl->setContent($conf->getHTML());
    }


    /**
     * cancelDelete
     */
    public function cancelDelete()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->redirect($this, "edit");
    }


    public function deleteView()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        if ($this->tableview_id && ilDclDetailedViewDefinition::exists($this->tableview_id)) {
            include_once("class.ilDclDetailedViewDefinition.php");
            $pageObject = new ilDclDetailedViewDefinition($this->tableview_id);
            $pageObject->delete();
        }

        ilUtil::sendSuccess($lng->txt("dcl_empty_detailed_view_success"), true);

        // Bug fix for mantis 22537: Redirect to settings-tab instead of fields-tab. This solves the problem and is more intuitive.
        $ilCtrl->redirectByClass("ilDclTableViewEditGUI", "editGeneralSettings");
    }


    /**
     * Release page lock
     * overwrite to redirect properly
     */
    public function releasePageLock()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->getPageObject()->releasePageLock();
        ilUtil::sendSuccess($lng->txt("cont_page_lock_released"), true);
        $ilCtrl->redirectByClass('ilDclTableViewGUI', "show");
    }


    /**
     * Finalizing output processing
     *
     * @param string $a_output
     *
     * @return string
     */
    public function postOutputProcessing($a_output)
    {
        // You can use this to parse placeholders and the like before outputting

        // user view (IL_PAGE_PRESENTATION?)
        if ($this->getOutputMode() == IL_PAGE_PREVIEW) {
            //page preview is not being used inside DataCollections - if you are here, something's probably wrong

            //			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
            //
            //			// :TODO: find a suitable presentation for matched placeholders
            //			$allp = ilDataCollectionRecordViewViewdefinition::getAvailablePlaceholders($this->table_id, true);
            //			foreach ($allp as $id => $item) {
            //				$parsed_item = new ilTextInputGUI("", "fields[" . $item->getId() . "]");
            //				$parsed_item = $parsed_item->getToolbarHTML();
            //
            //				$a_output = str_replace($id, $item->getTitle() . ": " . $parsed_item, $a_output);
            //			}
        } // editor
        else {
            if ($this->getOutputMode() == IL_PAGE_EDIT) {
                $allp = $this->getPageObject()->getAvailablePlaceholders();

                // :TODO: find a suitable markup for matched placeholders
                foreach ($allp as $item) {
                    $a_output = str_replace($item, "<span style=\"color:green\">" . $item . "</span>", $a_output);
                }
            }
        }

        return $a_output;
    }
}
