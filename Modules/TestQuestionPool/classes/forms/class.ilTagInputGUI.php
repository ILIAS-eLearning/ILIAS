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

        $values = array_filter($this->strArray($this->getPostVar()));
        if($values === [] && $this->getRequired()) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return $this->checkSubItemsInput();
    }

    /**
     * @param string    $a_mode
     * @return string
     */
    public function render(): string
    {
        $this->tpl->addJavaScript('Modules/TestQuestionPool/templates/default/tagInput.js');
        $config = [
            'min_length' => $this->type_ahead_min_length,
            'limit' => $this->type_ahead_limit,
            'highlight' => $this->type_ahead_highlight,
            'case' => '',
            'maxtags' => $this->max_tags,
            'maxchars' => $this->max_chars,
            'allow_duplicates' => $this->allow_duplicates
        ];
        if ($this->type_ahead_ignore_case) {
            $config['case'] = 'i';
        }

        $this->tpl->addOnLoadCode(
            'ilBootstrapTaggingOnLoad.initConfig(' . json_encode($config) . ');'
        );

        $tpl = new ilTemplate("tpl.prop_tag_typeahead.html", true, true, "Modules/TestQuestionPool");
        foreach ($this->getOptions() as $option_text) {
            $tpl->setCurrentBlock("prop_select_option");
            $tpl->setVariable("VAL_SELECT_OPTION", ilLegacyFormElementsUtil::prepareFormOutput($option_text));
            $tpl->setVariable("TXT_SELECT_OPTION", $option_text);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ID", $this->getFieldId());

        $tpl->setVariable("POST_VAR", $this->getPostVar() . "[]");

        if ($this->js_self_init) {
            $id = preg_replace('/[^\d]+/', '', $this->getFieldId());
            $this->tpl->addOnLoadCode(
                "ilBootstrapTaggingOnLoad.appendId('#" . $this->getFieldId() . "');\n"
                . "ilBootstrapTaggingOnLoad.appendTerms(" . $id . ", " . json_encode($this->type_ahead_list) . ");\n"
                . "ilBootstrapTaggingOnLoad.Init();"
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
