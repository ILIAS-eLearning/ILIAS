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
* This class represents a tag list property in a property form.
*
* @author Guido Vollbach <gvollbach@databay.de>
*/
class ilTagInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected ilGlobalTemplateInterface $tpl;

    protected $options = array();
    protected $max_tags = 0;
    protected $max_chars = 0;
    protected $allow_duplicates = false;
    protected $js_self_init = true;

    protected $type_ahead = false;
    protected $type_ahead_ignore_case = true;
    protected $type_ahead_list = array();
    protected $type_ahead_min_length = 2;
    protected $type_ahead_limit = 30;
    protected $type_ahead_highlight = true;

    /**
     * @param int $max_tags
     */
    public function setMaxTags($max_tags): void
    {
        $this->max_tags = $max_tags;
    }

    /**
     * @param int $max_chars
     */
    public function setMaxChars($max_chars): void
    {
        $this->max_chars = $max_chars;
    }

    /**
     * @param boolean $allow_duplicates
     */
    public function setAllowDuplicates($allow_duplicates): void
    {
        $this->allow_duplicates = $allow_duplicates;
    }

    /**
     * @param boolean $js_self_init
     */
    public function setJsSelfInit($js_self_init): void
    {
        $this->js_self_init = $js_self_init;
    }

    /**
     * @param boolean $type_ahead
     */
    public function setTypeAhead($type_ahead): void
    {
        $this->type_ahead = $type_ahead;
    }

    /**
     * @param boolean $type_ahead_ignore_case
     */
    public function setTypeAheadIgnoreCase($type_ahead_ignore_case): void
    {
        $this->type_ahead_ignore_case = $type_ahead_ignore_case;
    }

    /**
     * @param int $min_length
     */
    public function setTypeAheadMinLength($min_length): void
    {
        $this->type_ahead_min_length = $min_length;
    }

    /**
     * @param int $limit
     */
    public function setTypeAheadLimit($limit): void
    {
        $this->type_ahead_limit = $limit;
    }

    /**
     * @param boolean $highlight
     */
    public function setTypeAheadHighlight($highlight): void
    {
        $this->type_ahead_highlight = $highlight;
    }

    /**
     * @param array $type_ahead_list
     */
    public function setTypeAheadList($type_ahead_list): void
    {
        $this->type_ahead_list = $type_ahead_list;
    }

    /**
     * Set Options.
     *
     * @param	array	$a_options	Options.
     */
    public function setOptions($a_options): void
    {
        $this->options = $a_options;
    }

    /**
     * Get Options.
     *
     * @return	array	Options. Array
     */
    public function getOptions(): array
    {
        return $this->options ? $this->options : array();
    }

    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("tag_input");
        $this->tpl->addJavaScript('./Modules/TestQuestionPool/templates/default/bootstrap-tagsinput_2015_25_03.js');
        $this->tpl->addJavaScript('./Modules/TestQuestionPool/templates/default/typeahead_0.11.1.js');
        $this->tpl->addCss('./Modules/TestQuestionPool/templates/default/bootstrap-tagsinput_2015_25_03.css');
    }

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values): void
    {
        $this->setOptions($a_values[$this->getPostVar()]);
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return	boolean		Input ok, true/false
    */
    public function checkInput(): bool
    {
        $lng = $this->lng;

        $valid = true;
        if (array_key_exists($this->getPostVar(), $_POST)) {
            foreach ($_POST[$this->getPostVar()] as $idx => $value) {
                $_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
            }
            $_POST[$this->getPostVar()] = array_unique($_POST[$this->getPostVar()]);

            if ($this->getRequired() && !trim(implode("", $_POST[$this->getPostVar()]))) {
                $valid = false;
            }
        } elseif ($this->getRequired()) {
            $valid = false;
        }
        if (!$valid) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return $this->checkSubItemsInput();
    }

    /**
     * @param string    $a_mode
     * @return string
     */
    public function render($a_mode = ""): string
    {
        if ($this->type_ahead) {
            $tpl = new ilTemplate("tpl.prop_tag_typeahead.html", true, true, "Modules/TestQuestionPool");
            $tpl->setVariable("MIN_LENGTH", $this->type_ahead_min_length);
            $tpl->setVariable("LIMIT", $this->type_ahead_limit);
            $tpl->setVariable("HIGHLIGHT", $this->type_ahead_highlight);
            if ($this->type_ahead_ignore_case) {
                $tpl->setVariable("CASE", 'i');
            }
            $tpl->setVariable("TERMS", json_encode($this->type_ahead_list));
        } else {
            $tpl = new ilTemplate("tpl.prop_tag.html", true, true, "Services/Form");
        }

        $tpl->setVariable("MAXTAGS", $this->max_tags);
        $tpl->setVariable("MAXCHARS", $this->max_chars);
        $tpl->setVariable("ALLOW_DUPLICATES", $this->allow_duplicates);

        foreach ($this->getOptions() as $option_value => $option_text) {
            $tpl->setCurrentBlock("prop_select_option");
            $tpl->setVariable("VAL_SELECT_OPTION", ilLegacyFormElementsUtil::prepareFormOutput($option_text));
            $tpl->setVariable("TXT_SELECT_OPTION", $option_text);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ID", $this->getFieldId());

        $tpl->setVariable("POST_VAR", $this->getPostVar() . "[]");

        if ($this->js_self_init) {
            $this->tpl->addOnLoadCode(
                "ilBootstrapTaggingOnLoad.appendId('#" . $this->getFieldId() . "');\n" .
                "ilBootstrapTaggingOnLoad.Init();"
            );
        }
        return $tpl->get();
    }

    /**
     * @param $a_tpl
     */
    public function insert($a_tpl): void
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }
}
