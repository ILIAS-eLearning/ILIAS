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
 * User interface class for a checkbox list overlay
 *
 * @author Alexander Killing <killing@leifos.de>
 * @deprecated only used in legacy tables, do not introduce this anywhere else
 */
class ilCheckboxListOverlayGUI
{
    protected ilLanguage $lng;
    private array $items = array();
    protected string $id;
    protected string $link_title;
    protected string $selectionheaderclass;
    protected string $form_cmd;
    protected string $field_var;
    protected string $hidden_var;
    protected \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        string $a_id = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setId($a_id);
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function setId(string $a_val) : void
    {
        $this->id = $a_val;
    }
    
    public function getId() : string
    {
        return $this->id;
    }
    
    public function setLinkTitle(string $a_val) : void
    {
        $this->link_title = $a_val;
    }
    
    public function getLinkTitle() : string
    {
        return $this->link_title;
    }
    
    public function setItems(array $a_val) : void
    {
        $this->items = $a_val;
    }
    
    public function getItems() : array
    {
        return $this->items;
    }
    
    public function setSelectionHeaderClass(
        string $a_selectionheaderclass
    ) : void {
        $this->selectionheaderclass = $a_selectionheaderclass;
    }

    public function getSelectionHeaderClass() : string
    {
        return $this->selectionheaderclass;
    }

    public function setFormCmd(string $a_val) : void
    {
        $this->form_cmd = $a_val;
    }
    
    public function getFormCmd() : string
    {
        return $this->form_cmd;
    }
    
    public function setFieldVar(string $a_val) : void
    {
        $this->field_var = $a_val;
    }
    
    public function getFieldVar() : string
    {
        return $this->field_var;
    }
    
    public function setHiddenVar(string $a_val) : void
    {
        $this->hidden_var = $a_val;
    }
    
    public function getHiddenVar() : string
    {
        return $this->hidden_var;
    }

    public function getHTML(bool $pull_right = true) : string
    {
        $lng = $this->lng;
        
        $items = $this->getItems();

        $tpl = new ilTemplate(
            "tpl.checkbox_list_overlay.html",
            true,
            true,
            "Services/UIComponent/CheckboxListOverlay",
            "DEFAULT",
            false,
            true
        );

        $this->main_tpl->addOnLoadCode("$('#chkbxlstovl_" . $this->getId() . "').click(function(event){
			event.stopPropagation();
		});");

        $tpl->setCurrentBlock("top_img");
        
        // do not repeat title (accessibility) -> empty alt
        $tpl->setVariable("TXT_SEL_TOP", $this->getLinkTitle());

        $tpl->parseCurrentBlock();
        
        reset($items);
        $cnt = 0;
        foreach ($items as $k => $v) {
            $tpl->setCurrentBlock("list_entry");
            $tpl->setVariable("VAR", $this->getFieldVar());
            $tpl->setVariable("VAL_ENTRY", $k);
            $tpl->setVariable("TXT_ENTRY", $v["txt"]);
            $tpl->setVariable("IDX_ENTRY", ++$cnt);
            if ($v["selected"]) {
                $tpl->setVariable("CHECKED", "checked='checked'");
            }
            $tpl->parseCurrentBlock();
        }

        if ($pull_right) {
            $tpl->touchBlock("pr");
        }

        $tpl->setVariable("ID", $this->getId());
        $tpl->setVariable("HIDDEN_VAR", $this->getHiddenVar());
        $tpl->setVariable("CMD_SUBMIT", $this->getFormCmd());
        $tpl->setVariable("VAL_SUBMIT", $lng->txt("refresh"));
        return $tpl->get();
    }
}
