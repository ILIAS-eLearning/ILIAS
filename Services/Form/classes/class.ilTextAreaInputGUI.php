<?php

declare(strict_types=1);

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
 * This class represents a text area property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTextAreaInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected array $rteSupport = [];
    protected string $value = "";
    protected int $cols = 30;
    protected int $rows = 5;
    protected bool $usert = false;
    protected array $rtetags = [];
    protected array $plugins = [];
    protected array $removeplugins = [];
    protected array $buttons = [];
    protected array $rtesupport = [];
    protected bool $use_tags_for_rte_only = true;
    protected int $max_num_chars = 0;
    protected int $min_num_chars = 0;
    protected int $initial_rte_width = 795;
    protected array $disabled_buttons = array();
    protected bool $usePurifier = false;
    protected ?ilHtmlPurifierInterface $Purifier = null;
    protected ?string $root_block_element = "";

    protected array $rte_tag_set = array(
        "mini" => array("strong", "em", "u", "ol", "li", "ul", "blockquote", "a", "p", "span", "br"), // #13286/#17981
        "standard" => array("strong", "em", "u", "ol", "li", "ul", "p", "div",
            "i", "b", "code", "sup", "sub", "pre", "strike", "gap"),
        "extended" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","li","ol","p",
            "pre","span","strike","strong","sub","sup","u","ul",
            "i", "b", "gap"),
        "extended_img" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","img","li","ol","p",
            "pre","span","strike","strong","sub","sup","u","ul",
            "i", "b", "gap"),
        "extended_table" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","li","ol","p",
            "pre","span","strike","strong","sub","sup","table","td",
            "tr","u","ul", "i", "b", "gap"),
        "extended_table_img" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","img","li","ol","p",
            "pre","span","strike","strong","sub","sup","table","td",
            "tr","u","ul", "i", "b", "gap"),
        "full" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","img","li","ol","p",
            "pre","span","strike","strong","sub","sup","table","td",
            "tr","u","ul","ruby","rbc","rtc","rb","rt","rp", "i", "b", "gap"));

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("textarea");
        $this->setRteTagSet("standard");
        $this->plugins = array();
        $this->removeplugins = array();
        $this->buttons = array();
        $this->rteSupport = array();
    }

    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @deprecated
     */
    public function setCols(int $a_cols): void
    {
        // obsolete because of bootstrap
        $this->cols = $a_cols;
    }

    public function getCols(): int
    {
        return $this->cols;
    }

    public function setRows(int $a_rows): void
    {
        $this->rows = $a_rows;
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    // Set Maximum number of characters allowed.
    public function setMaxNumOfChars(int $a_number): void
    {
        $this->max_num_chars = $a_number;
    }

    public function getMaxNumOfChars(): int
    {
        return $this->max_num_chars;
    }

    public function setMinNumOfChars(int $a_number): void
    {
        $this->min_num_chars = $a_number;
    }

    public function getMinNumOfChars(): int
    {
        return $this->min_num_chars;
    }

    public function setUseRte(bool $a_usert, string $version = ''): void
    {
        $this->usert = $a_usert;
        $this->rteSupport['version'] = $version;
    }

    public function getUseRte(): bool
    {
        return $this->usert;
    }

    public function addPlugin(string $a_plugin): void
    {
        $this->plugins[$a_plugin] = $a_plugin;
    }

    public function removePlugin(string $a_plugin): void
    {
        $this->removeplugins[$a_plugin] = $a_plugin;
    }

    // Add RTE button.
    public function addButton(string $a_button): void
    {
        $this->buttons[$a_button] = $a_button;
    }

    public function removeButton(string $a_button): void
    {
        unset($this->buttons[$a_button]);
    }

    /**
     * Set RTE support for a special module
     */
    public function setRTESupport(
        int $obj_id,
        string $obj_type,
        string $module,
        ?string $cfg_template = null,
        bool $hide_switch = false,
        ?string $version = null
    ): void {
        $this->rteSupport = array(
            "obj_id" => $obj_id,
            "obj_type" => $obj_type,
            "module" => $module,
            'cfg_template' => $cfg_template,
            'hide_switch' => $hide_switch,
            'version' => $version
        );
    }

    public function removeRTESupport(): void
    {
        $this->rteSupport = array();
    }

    public function setRteTags(array $a_rtetags): void
    {
        $this->rtetags = $a_rtetags;
    }

    public function getRteTags(): array
    {
        return $this->rtetags;
    }

    /**
     * @param string $a_set_name Set name "standard", "extended", "extended_img",
     *					"extended_table", "extended_table_img", "full"
     */
    public function setRteTagSet(string $a_set_name): void
    {
        $this->setRteTags($this->rte_tag_set[$a_set_name]);
    }

    public function getRteTagSet($a_set_name): array
    {
        return $this->rte_tag_set[$a_set_name];
    }

    public function getRteTagString(): string
    {
        $result = "";
        foreach ($this->getRteTags() as $tag) {
            $result .= "<$tag>";
        }
        return $result;
    }

    /**
     * Set use tags for RTE only (default is true)
     */
    public function setUseTagsForRteOnly(bool $a_val): void
    {
        $this->use_tags_for_rte_only = $a_val;
    }

    public function getUseTagsForRteOnly(): bool
    {
        return $this->use_tags_for_rte_only;
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? "");

        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        $value = $this->getInput();
        if ($this->getRequired() && trim($value) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        if ($this->isCharLimited()) {
            //avoid whitespace surprises. #20630, #20674
            $ascii_whitespaces = chr(194) . chr(160);
            $ascii_breaklines = chr(13) . chr(10);

            $to_replace = array($ascii_whitespaces, $ascii_breaklines, "&lt;", "&gt;", "&amp;");
            $replace_to = array(' ', '', "_", "_", "_");

            #20630 mbstring extension is mandatory for 5.4
            $chars_entered = mb_strlen(strip_tags(str_replace($to_replace, $replace_to, $value)));

            if ($this->getMaxNumOfChars() && ($chars_entered > $this->getMaxNumOfChars())) {
                $this->setAlert($lng->txt("msg_input_char_limit_max"));
                return false;
            } elseif ($this->getMinNumOfChars() && ($chars_entered < $this->getMinNumOfChars())) {
                $this->setAlert($lng->txt("msg_input_char_limit_min"));
                return false;
            }
        }

        return $this->checkSubItemsInput();
    }

    public function getInput(): string
    {
        if ($this->usePurifier() && $this->getPurifier()) {
            $value = $this->getPurifier()->purify($this->raw($this->getPostVar()));
        } else {
            $allowed = $this->getRteTagString();
            if (isset($this->plugins["latex"]) && $this->plugins["latex"] == "latex" && !is_int(strpos($allowed, "<span>"))) {
                $allowed .= "<span>";
            }
            $value = ($this->getUseRte() || !$this->getUseTagsForRteOnly())
                ? ilUtil::stripSlashes($this->raw($this->getPostVar()), true, $allowed)
                : $this->str($this->getPostVar());
        }

        $value = self::removeProhibitedCharacters($value);
        return $value;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $lng = $this->lng;

        $ttpl = new ilTemplate("tpl.prop_textarea.html", true, true, "Services/Form");

        // disabled rte
        if ($this->getUseRte() && $this->getDisabled()) {
            $ttpl->setCurrentBlock("disabled_rte");
            $ttpl->setVariable("DR_VAL", $this->getValue());
        } else {
            if ($this->getUseRte()) {
                $rtestring = ilRTE::_getRTEClassname();
                $rte = new $rtestring((string) $this->rteSupport['version']);
                $rte->setInitialWidth($this->getInitialRteWidth());

                // @todo: Check this.
                $rte->addPlugin("emoticons");
                foreach ($this->plugins as $plugin) {
                    if (strlen($plugin)) {
                        $rte->addPlugin($plugin);
                    }
                }
                foreach ($this->removeplugins as $plugin) {
                    if (strlen($plugin)) {
                        $rte->removePlugin($plugin);
                    }
                }

                foreach ($this->buttons as $button) {
                    if (strlen($button)) {
                        $rte->addButton($button);
                    }
                }

                $rte->disableButtons($this->getDisabledButtons());

                if ($this->getRTERootBlockElement() !== null) {
                    $rte->setRTERootBlockElement($this->getRTERootBlockElement());
                }

                if (count($this->rteSupport) >= 3) {
                    $rte->addRTESupport($this->rteSupport["obj_id"], $this->rteSupport["obj_type"], $this->rteSupport["module"], false, $this->rteSupport['cfg_template'], $this->rteSupport['hide_switch']);
                } else {
                    // disable all plugins for mini-tagset
                    if (!array_diff($this->getRteTags(), $this->getRteTagSet("mini"))) {
                        $rte->removeAllPlugins();

                        // #13603 - "paste from word" is essential
                        $rte->addPlugin("paste");
                        //Add plugins 'lists', 'code' and 'link': in tinymce 3 it wasnt necessary to configure these plugins
                        $rte->addPlugin("lists");
                        $rte->addPlugin("link");
                        $rte->addPlugin("code");

                        if (method_exists($rte, 'removeAllContextMenuItems')) {
                            $rte->removeAllContextMenuItems(); //https://github.com/ILIAS-eLearning/ILIAS/pull/3088#issuecomment-805830050
                        }

                        // #11980 - p-tag is mandatory but we do not want the icons it comes with
                        $rte->disableButtons(array("anchor", "alignleft", "aligncenter",
                            "alignright", "alignjustify", "formatselect", "removeformat",
                            "cut", "copy", "paste", "pastetext")); // JF, 2013-12-09
                    }
                    $rte->addCustomRTESupport(0, "", $this->getRteTags());
                }

                $ttpl->touchBlock("prop_ta_w");
            } else {
                $ttpl->touchBlock("no_rteditor");

                if ($this->getCols() > 5) {
                    $ttpl->setCurrentBlock("prop_ta_c");
                    $ttpl->setVariable("COLS", $this->getCols());
                    $ttpl->parseCurrentBlock();
                } else {
                    $ttpl->touchBlock("prop_ta_w");
                }
            }
            $ttpl->setCurrentBlock("prop_textarea");
            $ttpl->setVariable("ROWS", $this->getRows());
            $ttpl->setVariable("POST_VAR", $this->getPostVar());
            $ttpl->setVariable("ID", $this->getFieldId());
            if ($this->getDisabled()) {
                $ttpl->setVariable('DISABLED', 'disabled="disabled" ');
            }
            $ttpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($this->getValue()));

            if ($this->getRequired()) {
                $ttpl->setVariable("REQUIRED", "required=\"required\"");
            }

            if ($this->isCharLimited()) {
                $ttpl->setVariable("MAXCHARS", $this->getMaxNumOfChars());
                $ttpl->setVariable("MINCHARS", $this->getMinNumOfChars());

                $lng->toJS("form_chars_remaining");
            }
        }
        $ttpl->parseCurrentBlock();

        if ($this->isCharLimited()) {
            $ttpl->setVariable("FEEDBACK_MAX_LIMIT", $this->getMaxNumOfChars());
            $ttpl->setVariable("FEEDBACK_ID", $this->getFieldId());
            $ttpl->setVariable("CHARS_REMAINING", $lng->txt("form_chars_remaining"));
        }

        if ($this->getDisabled()) {
            $ttpl->setVariable(
                "HIDDEN_INPUT",
                $this->getHiddenTag($this->getPostVar(), $this->getValue())
            );
        }
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $ttpl->get());
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Setter/Getter for the html purifier usage
     * @param bool $a_flag Use purifier or not
     * @return self|bool
     */
    public function usePurifier(bool $a_flag = null)
    {
        if (null === $a_flag) {
            return $this->usePurifier;
        }

        $this->usePurifier = $a_flag;
        return $this;
    }

    /**
     * Setter for the html purifier
     */
    public function setPurifier(ilHtmlPurifierInterface $Purifier): self
    {
        $this->Purifier = $Purifier;
        return $this;
    }

    public function getPurifier(): ilHtmlPurifierInterface
    {
        return $this->Purifier;
    }

    public function setRTERootBlockElement(string $a_root_block_element): self
    {
        $this->root_block_element = $a_root_block_element;
        return $this;
    }

    public function getRTERootBlockElement(): string
    {
        return $this->root_block_element;
    }

    /**
     * Sets buttons which should be disabled in TinyMCE
     *
     * @param string|string[] $a_button	Either a button string or an array of button strings
     */
    public function disableButtons($a_button): self
    {
        if (is_array($a_button)) {
            $this->disabled_buttons = array_unique(array_merge($this->disabled_buttons, $a_button));
        } else {
            $this->disabled_buttons = array_unique(array_merge($this->disabled_buttons, array($a_button)));
        }
        return $this;
    }

    /**
     * @return	string|array	Array of disabled buttons
     */
    public function getDisabledButtons(bool $as_array = true)
    {
        if (!$as_array) {
            return implode(',', $this->disabled_buttons);
        } else {
            return $this->disabled_buttons;
        }
    }

    public function getInitialRteWidth(): int
    {
        return $this->initial_rte_width;
    }

    public function setInitialRteWidth(int $initial_rte_width): void
    {
        $this->initial_rte_width = $initial_rte_width;
    }

    public function isCharLimited(): bool
    {
        if ($this->getMaxNumOfChars() || $this->getMinNumOfChars()) {
            return true;
        }

        return false;
    }
}
