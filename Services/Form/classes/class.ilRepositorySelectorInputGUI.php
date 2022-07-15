<?php declare(strict_types=1);

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
 * This class represents a repository selector in a property form.
 *
 * The implementation is kind of beta. It looses all other inputs, if the
 * selector link is used.
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_IsCalledBy ilRepositorySelectorInputGUI: ilFormPropertyDispatchGUI
 */
class ilRepositorySelectorInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
    protected array $clickable_types = [];
    protected string $hm = "";
    protected string $select_text = "";
    protected ilGlobalTemplateInterface $tpl;
    protected ilTree $tree;
    protected ilObjUser $user;
    protected ilObjectDataCache $obj_data_cache;
    protected array $options = [];
    protected int $value = 0;
    protected array $container_types = array("root", "cat", "grp", "fold", "crs");
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $lng = $DIC->language();
        
        parent::__construct($a_title, $a_postvar);
        $this->setClickableTypes($this->container_types);
        $this->setHeaderMessage($lng->txt('search_area_info'));
        $this->setType("rep_select");
        $this->setSelectText($lng->txt("select"));
    }

    /**
     * @param int|string $a_value
     * @return void
     */
    public function setValue($a_value) : void
    {
        $this->value = (int) $a_value;
    }

    public function getValue() : int
    {
        return $this->value;
    }
    
    public function setValueByArray(array $a_values) : void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? "");
    }

    public function setSelectText(string $a_val) : void
    {
        $this->select_text = $a_val;
    }
    
    public function getSelectText() : string
    {
        return $this->select_text;
    }
    
    public function setHeaderMessage(string $a_val) : void
    {
        $this->hm = $a_val;
    }

    public function getHeaderMessage() : string
    {
        return $this->hm;
    }
    
    public function setClickableTypes(array $a_types) : void
    {
        $this->clickable_types = $a_types;
    }
    
    public function getClickableTypes() : array
    {
        return $this->clickable_types;
    }
    
    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        if ($this->getRequired() && trim($this->str($this->getPostVar())) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return true;
    }

    public function getInput() : int
    {
        return (int) trim($this->str($this->getPostVar()));
    }

    public function showRepositorySelection() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "postvar", $this->getPostVar());

        $this->tpl->setOnScreenMessage('info', $this->getHeaderMessage());

        $exp = new ilRepositorySelectorExplorerGUI(
            $this,
            "showRepositorySelection",
            $this,
            "selectRepositoryItem",
            "root_id"
        );
        $exp->setTypeWhiteList($this->getVisibleTypes());
        $exp->setClickableTypes($this->getClickableTypes());

        if ($this->getValue()) {
            $exp->setPathOpen($this->getValue());
            $exp->setHighlightedNode((string) $this->getHighlightedNode());
        }

        if ($exp->handleCommand()) {
            return;
        }
        // build html-output
        $tpl->setContent($exp->getHTML());
    }
    
    public function selectRepositoryItem() : void
    {
        $ilCtrl = $this->ctrl;

        $this->setValue((string) $this->int("root_id"));
        $this->writeToSession();

        $ilCtrl->returnToParent($this);
    }
    
    public function reset() : void
    {
        $ilCtrl = $this->ctrl;

        $this->setValue("");
        $this->writeToSession();

        $ilCtrl->returnToParent($this);
    }
    
    public function render($a_mode = "property_form") : string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilObjDataCache = $this->obj_data_cache;
        $tree = $this->tree;
        $parent_gui = "";
        
        $tpl = new ilTemplate("tpl.prop_rep_select.html", true, true, "Services/Form");

        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput((string) $this->getValue()));
        $tpl->setVariable("TXT_SELECT", $this->getSelectText());
        $tpl->setVariable("TXT_RESET", $lng->txt("reset"));
        switch ($a_mode) {
            case "property_form":
                $parent_gui = "ilpropertyformgui";
                break;
                
            case "table_filter":
                $parent_gui = get_class($this->getParentTable());
                break;
        }

        $ilCtrl->setParameterByClass(
            "ilrepositoryselectorinputgui",
            "postvar",
            $this->getPostVar()
        );
        $tpl->setVariable(
            "HREF_SELECT",
            $ilCtrl->getLinkTargetByClass(
                array($parent_gui, "ilformpropertydispatchgui", "ilrepositoryselectorinputgui"),
                "showRepositorySelection"
            )
        );
        $tpl->setVariable(
            "HREF_RESET",
            $ilCtrl->getLinkTargetByClass(
                array($parent_gui, "ilformpropertydispatchgui", "ilrepositoryselectorinputgui"),
                "reset"
            )
        );

        if ($this->getValue() > 0 && $this->getValue() != ROOT_FOLDER_ID) {
            $tpl->setVariable(
                "TXT_ITEM",
                $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($this->getValue()))
            );
        } else {
            $nd = $tree->getNodeData(ROOT_FOLDER_ID);
            $title = $nd["title"];
            if ($title == "ILIAS") {
                $title = $lng->txt("repository");
            }
            if (in_array($nd["type"], $this->getClickableTypes())) {
                $tpl->setVariable("TXT_ITEM", $title);
            }
        }
        return $tpl->get();
    }
    
    public function insert(ilTemplate $a_tpl) : void
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    public function getTableFilterHTML() : string
    {
        $html = $this->render("table_filter");
        return $html;
    }

    protected function getHighlightedNode() : int
    {
        $tree = $this->tree;

        if (!in_array(ilObject::_lookupType($this->getValue(), true), $this->getVisibleTypes())) {
            return $tree->getParentId($this->getValue());
        }

        return $this->getValue();
    }

    protected function getVisibleTypes() : array
    {
        return array_merge($this->container_types, $this->getClickableTypes());
    }
}
