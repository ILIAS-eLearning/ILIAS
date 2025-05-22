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
    protected ?ilDclBaseRecordModel $record = null;

    public function __construct(int $tableview_id)
    {
        global $DIC;

        $this->tableview_id = $tableview_id;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->locator = $DIC['ilLocator'];

        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        $this->setStyleId($DIC->contentStyle()->domain()->styleForRefId($ref_id)->getEffectiveStyleId());

        if (!ilPageObject::_exists('dclf', $tableview_id)) {
            $viewdef = new ilDclDetailedViewDefinition();
            $viewdef->setId($tableview_id);
            $viewdef->setParentId(ilObject2::_lookupObjectId($ref_id));
            $viewdef->create();
        } elseif (!ilPageObject::_lookupActive($tableview_id, 'dclf')) {
            $page = new ilDclDetailedViewDefinition($tableview_id);
            $page->setActive(true);
            foreach ($page->getAllPCIds() as $id) {
                $page->getContentObjectForPcId($id)->disable();
            }
            $page->update();
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

    public function setRecord(ilDclBaseRecordModel $record): void
    {
        $this->record = $record;
    }

    /**
     * execute command
     */
    public function executeCommand(): string
    {
        $this->ctrl->setParameter($this, "dclv", $this->getPageObject()->getId());
        $title = $this->lng->txt("dcl_view_viewdefinition");
        $this->setPresentationTitle($title);
        $this->locator->addItem($title, $this->ctrl->getLinkTarget($this, "preview"));
        return parent::executeCommand();
    }

    public function showPage(): string
    {
        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath($this->getStyleId()));
        if ($this->getOutputMode() === ilPageObjectGUI::EDIT) {
            $legend = $this->getPageObject()->getAvailablePlaceholders();
            if (sizeof($legend)) {
                $html = "<span class=\"small\">" . $this->lng->txt("dcl_legend_placeholders") . ":<br>";
                foreach ($legend as $field) {
                    $html .= "[[" . $field->getID() . ']]<i style="opacity: 0.3"> - ' . $field->getTitle() . '</i><br>';
                }
                $this->setPrependingHtml($html . "</span>");
            }
        }

        return parent::showPage();
    }

    public function postOutputProcessing(string $a_output): string
    {
        foreach ($this->getPageObject()->getAvailablePlaceholders() as $field) {
            if ($this->record === null) {
                $replacement = '<b>' . $field->getTitle() . '</b>';
            } else {
                $replacement = $this->record->getRecordFieldSingleHTML($field->getId());
            }
            $a_output = str_replace('[[' . $field->getId() . ']]', $replacement, $a_output);
        }

        return $a_output;
    }
}
