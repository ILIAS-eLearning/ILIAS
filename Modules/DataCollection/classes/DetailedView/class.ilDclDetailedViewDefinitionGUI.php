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

declare(strict_types=1);

/**
 * @ilCtrl_Calls ilDclDetailedViewDefinitionGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilDclDetailedViewDefinitionGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 */
class ilDclDetailedViewDefinitionGUI extends ilPageObjectGUI
{
    private ilLocatorGUI $locator;
    protected int $tableview_id;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;

    public function __construct(int $tableview_id)
    {
        global $DIC;

        $this->tableview_id = $tableview_id;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->locator = $DIC['ilLocator'];

        // we always need a page object - create on demand
        if (!ilPageObject::_exists('dclf', $tableview_id)) {
            $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());

            $viewdef = new ilDclDetailedViewDefinition();
            $viewdef->setId($tableview_id);
            $viewdef->setParentId(ilObject2::_lookupObjectId($ref_id));
            $viewdef->setActive(false);
            $viewdef->create();
        }

        parent::__construct("dclf", $tableview_id);

        // content style (using system defaults)
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->parseCurrentBlock();
    }

    /**
     * execute command
     */
    public function executeCommand(): string
    {
        $next_class = $this->ctrl->getNextClass($this);

        $viewdef = $this->getPageObject();
        $this->ctrl->setParameter($this, "dclv", $viewdef->getId());
        $title = $this->lng->txt("dcl_view_viewdefinition");

        switch ($next_class) {
            case "ilpageobjectgui":
                throw new ilCOPageException("Deprecated. ilDclDetailedViewDefinitionGUI gui forwarding to ilpageobject");
            default:
                $this->setPresentationTitle($title);
                $this->locator->addItem($title, $this->ctrl->getLinkTarget($this, "preview"));

                return parent::executeCommand();
        }
    }

    public function showPage(): string
    {
        if ($this->getOutputMode() == ilPageObjectGUI::EDIT) {
            $delete_button = $this->ui->factory()->button()->standard(
                $this->lng->txt('dcl_empty_detailed_view'),
                $this->ctrl->getLinkTarget($this, 'confirmDelete')
            );
            $this->toolbar->addComponent($delete_button);

            if ($this->getPageObject()->getActive()) {
                $activation_button = $this->ui->factory()->button()->standard(
                    $this->lng->txt('dcl_deactivate_view'),
                    $this->ctrl->getLinkTarget($this, 'deactivate')
                );
            } else {
                $activation_button = $this->ui->factory()->button()->standard(
                    $this->lng->txt('dcl_activate_view'),
                    $this->ctrl->getLinkTarget($this, 'activate')
                );
            }

            $this->toolbar->addComponent($activation_button);

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

    protected function activate(): void
    {
        $page = $this->getPageObject();
        $page->setActive(true);
        $page->update();
        $this->ctrl->redirect($this, 'edit');
    }

    protected function deactivate(): void
    {
        $page = $this->getPageObject();
        $page->setActive(false);
        $page->update();
        $this->ctrl->redirect($this, 'edit');
    }

    public function confirmDelete(): void
    {
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_confirm_delete_detailed_view_title'));

        $conf->addItem('tableview', (string)$this->tableview_id, $this->lng->txt('dcl_confirm_delete_detailed_view_text'));

        $conf->setConfirm($this->lng->txt('delete'), 'deleteView');
        $conf->setCancel($this->lng->txt('cancel'), 'cancelDelete');

        $this->tpl->setContent($conf->getHTML());
    }

    public function cancelDelete(): void
    {
        $this->ctrl->redirect($this, "edit");
    }

    public function deleteView(): void
    {
        if ($this->tableview_id && ilDclDetailedViewDefinition::exists($this->tableview_id)) {
            $pageObject = new ilDclDetailedViewDefinition($this->tableview_id);
            $pageObject->delete();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("dcl_empty_detailed_view_success"), true);
        $this->ctrl->redirectByClass(self::class, "edit");
    }

    /**
     * Release page lock
     * overwrite to redirect properly
     */
    public function releasePageLock(): void
    {
        $this->getPageObject()->releasePageLock();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("cont_page_lock_released"), true);
        $this->ctrl->redirectByClass('ilDclTableViewGUI', "show");
    }

    /**
     * Finalizing output processing
     */
    public function postOutputProcessing(string $a_output): string
    {
        // You can use this to parse placeholders and the like before outputting

        if ($this->getOutputMode() == ilPageObjectGUI::PREVIEW) {
            //page preview is not being used inside DataCollections - if you are here, something's probably wrong

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
            if ($this->getOutputMode() == ilPageObjectGUI::EDIT) {
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
