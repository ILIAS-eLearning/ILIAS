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

    protected ?array $options = null;
    protected int $max_tags = 0;
    protected int $max_chars = 0;
    protected bool $allow_duplicates = false;
    protected bool $js_self_init = true;

    protected bool $type_ahead_ignore_case = true;
    protected array $type_ahead_list = [];
    protected int $type_ahead_min_length = 2;
    protected int $type_ahead_limit = 30;
    protected bool $type_ahead_highlight = true;

    public function setMaxTags(int $max_tags): void
    {
        $this->max_tags = $max_tags;
    }

    public function setMaxChars(int $max_chars): void
    {
        $this->max_chars = $max_chars;
    }

    public function setAllowDuplicates(bool $allow_duplicates): void
    {
        $this->allow_duplicates = $allow_duplicates;
    }

    public function setJsSelfInit(bool $js_self_init): void
    {
        $this->js_self_init = $js_self_init;
    }

    public function setTypeAheadIgnoreCase(bool $type_ahead_ignore_case): void
    {
        $this->type_ahead_ignore_case = $type_ahead_ignore_case;
    }

    public function setTypeAheadMinLength(int $min_length): void
    {
        $this->type_ahead_min_length = $min_length;
    }

    public function setTypeAheadLimit(int $limit): void
    {
        $this->type_ahead_limit = $limit;
    }

    public function setTypeAheadHighlight(bool $highlight): void
    {
        $this->type_ahead_highlight = $highlight;
    }

    public function setTypeAheadList(array $type_ahead_list): void
    {
        $this->type_ahead_list = $type_ahead_list;
    }

    public function setOptions(?array $a_options): void
    {
        $this->options = $a_options;
    }

    public function getOptions(): array
    {
        return $this->options ? $this->options : [];
    }

    public function __construct(string $a_title = '', string $a_postvar = '')
    {
        global $DIC;
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType('tag_input');
        $this->tpl->addJavaScript('assets/js/bootstrap-tagsinput_2015_25_03.js');
        $this->tpl->addJavaScript('assets/js/typeahead_0.11.1.js');
        $this->tpl->addCss('assets/css/bootstrap-tagsinput_2015_25_03.css');
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

    public function checkInput(): bool
    {
        $lng = $this->lng;
        $valid = true;

        $values = array_filter($this->strArray($this->getPostVar()));
        if ($values === [] && $this->getRequired()) {
            $this->setAlert($lng->txt(msg_input_is_required));
            return false;
        }
        return $this->checkSubItemsInput();
    }

    public function render(): string
    {
        $this->tpl->addJavaScript('assets/js/testQuestionPoolTagInput.js');
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

        $tpl = new ilTemplate('tpl.prop_tag_typeahead.html', true, true, 'components/ILIAS/TestQuestionPool');
        foreach ($this->getOptions() as $option_text) {
            $tpl->setCurrentBlock('prop_select_option');
            $tpl->setVariable('VAL_SELECT_OPTION', ilLegacyFormElementsUtil::prepareFormOutput($option_text));
            $tpl->setVariable('TXT_SELECT_OPTION', $option_text);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('ID', $this->getFieldId());

        $tpl->setVariable('POST_VAR', $this->getPostVar() . '[]');

        if ($this->js_self_init) {
            $id = preg_replace('/[^\d]+/', '', $this->getFieldId());
            $this->tpl->addOnLoadCode(
                "ilBootstrapTaggingOnLoad.appendId('#{$this->getFieldId()}');\n"
                . "ilBootstrapTaggingOnLoad.appendTerms({$id}, " . json_encode($this->type_ahead_list) . ");\n"
                . 'ilBootstrapTaggingOnLoad.Init();'
            );
        }
        return $tpl->get();
    }

    public function insert(ilTemplate $tpl): void
    {
        $tpl->setCurrentBlock('prop_generic');
        $tpl->setVariable('PROP_GENERIC', $this->render());
        $tpl->parseCurrentBlock();
    }
}
