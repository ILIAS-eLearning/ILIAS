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
 ********************************************************************
 */

/**
 * Class ilDclDatatype
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Marcel Raimann <mr@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       Oskar Truffer <ot@studer-raimann.ch>
 * @version      $Id:
 * @ingroup      ModulesDataCollection
 * @ilCtrl_Calls ilDclTreePickInputGUI : ilDclRecordEditGUI
 */
class ilDclTreePickInputGUI extends ilCustomInputGUI
{
    protected ilTextInputGUI $title_input;
    protected ilTextInputGUI $search_input;
    protected ilHiddenInputGUI $hidden_input;

    public function __construct(string $title, string $post_var)
    {
        global $DIC;
        $lng = $DIC['lng'];
        /** @var \ilGlobalTemplateInterface $tpl */
        $tpl = $DIC->ui()->mainTemplate();
        /**
         * @var $tpl iltemplate
         */
        parent::__construct($title, $post_var);
        $tpl->addJavaScript('./Modules/DataCollection/js/ilDclTreeSearch.js');
        $this->title_input = new ilTextInputGUI($this->getTitle(), "display_" . $this->getPostVar());
        $this->title_input->setDisabled(true);
        $this->title_input->setInfo($lng->txt("dcl_ilias_refere	nce_info"));
        $this->title_input->setInlineStyle('width: 98%; display:inline-block;');
        $this->search_input = new ilTextInputGUI($this->getTitle(), "search_" . $this->getPostVar());
        $this->search_input->setDisabled(false);
        $this->search_input->setInfo($lng->txt('dcl_ilias_reference_info'));
        $this->search_input->setInlineStyle('width: 98%; margin-top: 5px;');
        $this->hidden_input = new ilHiddenInputGUI($this->getPostVar());
        $this->lng = $lng;
    }

    public function getHtml(): string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = new ilTemplate("tpl.dcl_tree.html", true, true, "Modules/DataCollection");
        $tpl->setVariable("FIELD_ID", $this->getPostVar());
        $tpl->setVariable("AJAX_LINK", $ilCtrl->getLinkTargetByClass("ildclrecordeditgui", "searchObjects"));
        $tpl->setVariable("LOADER_PATH", ilUtil::getImagePath("loader.svg"));
        $out = $this->title_input->getToolbarHTML();
        $out .= "<a href='#' style='display:inline-block;' id='remove_{$this->getPostVar()}'>" . ilGlyphGUI::get(ilGlyphGUI::REMOVE) . "</a>";
        $out .= $this->search_input->getTableFilterHTML();
        $out .= $this->hidden_input->getToolbarHTML();
        $out .= "<a href='#' id='search_button_" . $this->getPostVar() . "'>" . $this->lng->txt('search') . "</a>";
        $out .= $tpl->get();

        return $out;
    }

    public function setValueByArray(array $values): void
    {
        parent::setValueByArray($values);
        $path = new ilPathGUI();
        $reference = $values[$this->getPostVar()];
        if ($reference) {
            $pathString = $path->getPath(ROOT_FOLDER_ID, (int) $reference);
            $id = ilObject::_lookupObjId($reference);
            $this->title_input->setValue($pathString . " > " . ilObject::_lookupTitle($id));
            $this->hidden_input->setValue($reference);
        }
    }
}
