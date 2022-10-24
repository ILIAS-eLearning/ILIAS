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
 * Select explorer tree nodes input GUI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilExplorerSelectInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
    /**
     * @var string|int|array
     */
    protected $value;
    protected bool $multi_nodes;
    protected ilExplorerBaseGUI $explorer_gui;
    protected bool $disabled = false;

    public function __construct(
        string $a_title,
        string $a_postvar,
        ilExplorerBaseGUI $a_explorer_gui,
        bool $a_multi = false
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->multi_nodes = $a_multi;
        $this->explorer_gui = $a_explorer_gui;
        $this->global_template = $DIC['tpl'];

        parent::__construct($a_title, $a_postvar);
        $this->setType("exp_select");
    }

    /**
     * Get explorer handle command function
     */
    public function getExplHandleCmd(): string
    {
        return "handleExplorerCommand";
    }

    /**
     * Handle explorer command
     */
    public function handleExplorerCommand(): void
    {
        $val = $this->getValue();
        if (is_array($val)) {
            foreach ($val as $v) {
                $this->explorer_gui->setNodeOpen($v);
                $this->explorer_gui->setNodeSelected($v);
            }
        } elseif ($val != "") {
            $this->explorer_gui->setNodeOpen($val);
            $this->explorer_gui->setNodeSelected($val);
        }
        $this->explorer_gui->handleCommand();
    }

    /**
     * Get title for node id (needs to be overwritten, if explorer is not a tree eplorer
     */
    abstract public function getTitleForNodeId($a_id): string;

    /**
     * @param string|int|array node id or array of node ids (multi mode)
     */
    public function setValue($a_value): void
    {
        if ($this->multi_nodes && !is_array($a_value)) {
            if ($a_value !== false) {
                $this->value = array($a_value);
            } else {
                $this->value = array();
            }
        } else {
            $this->value = $a_value;
        }
    }

    /**
     * @return string|int|array node id or array of node ids (multi mode)
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value by array
     */
    public function setValueByArray(array $a_values): void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? "");
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     * @return    bool        Input ok, true/false
     */
    public function checkInput(): bool
    {
        $lng = $this->lng;

        // check required
        if ($this->getRequired()) {
            if ((!$this->multi_nodes && trim($this->getInput()) === "") ||
                ($this->multi_nodes && count($this->getInput()) === 0)) {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            }
        }
        return true;
    }

    /**
     * @return string|string[]
     */
    public function getInput()
    {
        if ($this->multi_nodes) {
            return $this->strArray($this->getPostVar());
        } else {
            return $this->str($this->getPostVar());
        }
    }

    /**
     * Render item
     */
    public function render(string $a_mode = "property_form"): string
    {
        $lng = $this->lng;

        $this->global_tpl->addJavascript("./Services/UIComponent/Explorer2/js/Explorer2.js");
        $this->global_tpl->addJavascript("./Services/UIComponent/Modal/js/Modal.js");
        $this->global_tpl->addOnLoadCode(
            "il.Explorer2.initSelect('" . $this->getFieldId() . "');"
        );

        $tpl = new ilTemplate("tpl.prop_expl_select.html", true, true, "Services/UIComponent/Explorer2");

        if ($a_mode !== "property_form") {
            $tpl->touchBlock("tiny_presentation");
        }

        // set values
        $val = $this->getValue();
        if (is_array($val)) {
            $val_txt = $sep = "";
            foreach ($val as $v) {
                $tpl->setCurrentBlock("node_hid");
                $tpl->setVariable("HID_NAME", $this->getPostVar() . "[]");
                $tpl->setVariable("HID_VAL", $v);
                $tpl->parseCurrentBlock();
                $val_txt .= $sep . $this->getTitleForNodeId($v);
                $sep = ", ";
                $this->explorer_gui->setNodeOpen($v);
                $this->explorer_gui->setNodeSelected($v);
            }
            $tpl->setVariable("VAL_TXT", $val_txt);
        } elseif ($val != "") {
            $tpl->setCurrentBlock("node_hid");
            $tpl->setVariable("HID_NAME", $this->getPostVar());
            $tpl->setVariable("HID_VAL", $val);
            $tpl->parseCurrentBlock();
            $tpl->setVariable("VAL_TXT", $this->getTitleForNodeId($val));
            $this->explorer_gui->setNodeOpen($val);
            $this->explorer_gui->setNodeSelected($val);
        }

        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("ID", $this->getFieldId());
        $ol_js = "il.Explorer2.initSelect('" . $this->getFieldId() . "');";
        $this->global_template->addOnLoadCode($ol_js);

        //		$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));

        //added disabled
        if (!$this->disabled) {
            $tpl->setCurrentBlock("txt_select");
            $tpl->setVariable("TXT_SELECT", $lng->txt("select"));
            $tpl->setVariable("ID_TXT_SELECT", $this->getFieldId());
            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("txt_reset");
            $tpl->setVariable("TXT_RESET", $lng->txt("reset"));
            $tpl->setVariable("ID_TXT_RESET", $this->getFieldId());
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("EXPL", $this->explorer_gui->getHTML());

        $top_tb = new ilToolbarGUI();

        $button = ilLinkButton::getInstance();
        $button->setCaption("select");
        $button->addCSSClass("ilExplSelectInputButS");
        $button->setOmitPreventDoubleSubmission(true);
        $top_tb->addStickyItem($button);

        $button = ilLinkButton::getInstance();
        $button->setCaption("cancel");
        $button->addCSSClass("ilExplSelectInputButC");
        $button->setOmitPreventDoubleSubmission(true);
        $top_tb->addStickyItem($button);

        // :TODO: we should probably clone the buttons properly
        $tpl->setVariable("TOP_TB", $top_tb->getHTML());
        $tpl->setVariable("BOT_TB", $top_tb->getHTML());

        return $tpl->get();
    }

    /**
     * Insert property html
     */
    public function insert(ilTemplate $a_tpl): void
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Get HTML for table filter
     */
    public function getTableFilterHTML(): string
    {
        $html = $this->render("table_filter");
        return $html;
    }
}
