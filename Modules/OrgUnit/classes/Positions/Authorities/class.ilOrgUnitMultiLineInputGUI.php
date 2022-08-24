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
 * Class ilOrgUnitMultiLineInputGUI
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitMultiLineInputGUI extends \ilFormPropertyGUI
{
    public const HOOK_IS_LINE_REMOVABLE = "hook_is_line_removable";
    public const HOOK_IS_INPUT_DISABLED = "hook_is_disabled";
    public const HOOK_BEFORE_INPUT_RENDER = "hook_before_render";
    protected array $cust_attr = array();
    protected $value;
    protected array $inputs = array();
    protected array $input_options = array();
    /** @var string[] */
    protected array $hooks = array();
    protected array $line_values = array();
    protected string $template_dir = '';
    protected array $post_var_cache = array();
    protected bool $show_label = false;
    protected bool $show_label_once = false;
    protected array $hidden_inputs = array();
    protected bool $position_movable = false;
    protected int $counter = 0;
    protected bool $show_info = false;

    /**
     * Constructor
     * @param string $a_title   Title
     * @param string $a_postvar Post Variable
     */
    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType("line_select");
        $this->setMulti(true);
        $this->initCSSandJS();
    }

    public function getHook(string $key): ?string
    {
        if (isset($this->hooks[$key])) {
            return $this->hooks[$key];
        }

        return false;
    }

    public function addHook(string $key, array $options): void
    {
        $this->hooks[$key] = $options;
    }

    public function removeHook(string $key): void
    {
        if (isset($this->hooks[$key])) {
            unset($this->hooks[$key]);
        }
    }

    public function addInput(\ilFormPropertyGUI $input, array $options = array()): void
    {
        $this->inputs[$input->getPostVar()] = $input;
        $this->input_options[$input->getPostVar()] = $options;
        $this->counter++;
    }

    public function getTemplateDir(): string
    {
        return $this->template_dir;
    }

    public function setTemplateDir(string $template_dir): void
    {
        $this->template_dir = $template_dir;
    }

    public function isShowLabel(): bool
    {
        return $this->show_label;
    }

    public function setShowLabel(bool $show_label)
    {
        $this->show_label = $show_label;
    }

    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function setMulti(bool $a_multi, bool $a_sortable = false, bool $a_addremove = true): void
    {
        $this->multi = $a_multi;
    }

    public function setValue(string $a_value): void
    {
        foreach ($this->inputs as $key => $item) {
            if (method_exists($item, 'setValue')) {
                $item->setValue($a_value[$key]);
            } elseif ($item instanceof \ilDateTimeInputGUI) {
                $item->setDate(new \ilDate($a_value[$key]['date'], IL_CAL_DATE));
            }
        }
        $this->value = $a_value;
    }

    public function getValue(): array
    {
        $out = array();
        foreach ($this->inputs as $key => $item) {
            $out[$key] = $item->getValue();
        }

        return $out;
    }

    /**
     * Set value by array
     * @param array $a_values value array
     */
    public function setValueByArray(array $a_values): void
    {
        $data = $a_values[$this->getPostVar()];
        if ($this->getMulti()) {
            $this->line_values = $data;
        } else {
            $this->setValue($data);
        }
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     * @return    bool        Input ok, true/false
     */
    public function checkInput(): bool
    {
        global $lng;
        $valid = true;
        // escape data
        $out_array = array();
        foreach ($_POST[$this->getPostVar()] as $item_num => $item) {
            foreach ($this->inputs as $input_key => $input) {
                if (isset($item[$input_key])) {
                    $out_array[$item_num][$input_key] = (is_string($item[$input_key])) ? \ilUtil::stripSlashes($item[$input_key]) : $item[$input_key];
                }
            }
        }
        $_POST[$this->getPostVar()] = $out_array;
        if ($this->getRequired() && !trim(implode("", $_POST[$this->getPostVar()]))) {
            $valid = false;
        }
        // validate
        foreach ($this->inputs as $input_key => $inputs) {
            if (!$inputs->checkInput()) {
                $valid = false;
            }
        }
        if (!$valid) {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }

        return $valid;
    }

    public function addCustomAttribute(string $key, string $value, bool $override = false): void
    {
        if (isset($this->cust_attr[$key]) && !$override) {
            $this->cust_attr[$key] .= ' ' . $value;
        } else {
            $this->cust_attr[$key] = $value;
        }
    }

    public function getCustomAttributes(): array
    {
        return (array) $this->cust_attr;
    }

    private function createInputPostVar(string $iterator_id, \ilFormPropertyGUI $input): string
    {
        if ($this->getMulti()) {
            return $this->getPostVar() . '[' . $iterator_id . '][' . $input->getPostVar() . ']';
        } else {
            return $this->getPostVar() . '[' . $input->getPostVar() . ']';
        }
    }

    /**
     * @throws ilTemplateException
     * @throws ilException
     */
    public function render(int $iterator_id = 0, bool $clean_render = false): string
    {
        $first_label = true;
        $tpl = new \ilTemplate(
            "tpl.multi_line_input.html",
            true,
            true,
            'Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting'
        );
        $class = 'multi_input_line';
        $this->addCustomAttribute('class', $class, true);
        foreach ($this->getCustomAttributes() as $key => $value) {
            $tpl->setCurrentBlock('cust_attr');
            $tpl->setVariable('CUSTOM_ATTR_KEY', $key);
            $tpl->setVariable('CUSTOM_ATTR_VALUE', $value);
            $tpl->parseCurrentBlock();
        }
        $inputs = $this->inputs;
        foreach ($inputs as $key => $input) {
            $input = clone $input;
            $is_hidden = false;
            $is_ta = false;
            if (!method_exists($input, 'render')) {
                switch (true) {
                    case ($input instanceof \ilHiddenInputGUI):
                        $is_hidden = true;
                        break;
                    case ($input instanceof \ilTextAreaInputGUI):
                        $is_ta = true;
                        break;
                    default:
                        throw new \ilException("Method " . get_class($input)
                            . "::render() does not exists! You cannot use this input-type in ilMultiLineInputGUI");
                }
            }

            $is_disabled_hook = $this->getHook(self::HOOK_IS_INPUT_DISABLED);
            if ($is_disabled_hook !== false && !$clean_render) {
                $input->setDisabled($is_disabled_hook($this->getValue()));
            }
            if ($this->getDisabled()) {
                $input->setDisabled(true);
            }
            if ($iterator_id == 0 && !isset($this->post_var_cache[$key])) {
                $this->post_var_cache[$key] = $input->getPostVar();
            } else {
                // Reset post var
                $input->setPostVar($this->post_var_cache[$key]);
            }
            $post_var = $this->createInputPostVar($iterator_id, $input);
            $input->setPostVar($post_var);
            $before_render_hook = $this->getHook(self::HOOK_BEFORE_INPUT_RENDER);
            if ($before_render_hook !== false && !$clean_render) {
                $input = $before_render_hook($this->getValue(), $key, $input);
            }
            switch (true) {
                case $is_hidden:
                    $tpl->setCurrentBlock('hidden');
                    $tpl->setVariable('NAME', $post_var);
                    $tpl->setVariable('VALUE', ilLegacyFormElementsUtil::prepareFormOutput($input->getValue()));
                    break;
                case $is_ta:
                    if ($this->isShowLabel() || ($this->isShowLabelOnce() && $first_label)) {
                        $tpl->setCurrentBlock('input_label');
                        $tpl->setVariable('LABEL', $input->getTitle());
                        $tpl->setVariable('CONTENT', $input->getHTML());
                        $tpl->parseCurrentBlock();
                        $first_label = false;
                    } else {
                        $tpl->setCurrentBlock('input');
                        $tpl->setVariable('CONTENT', $input->getHTML());
                    }
                    break;
                default:
                    if ($this->isShowLabel() || ($this->isShowLabelOnce() && $first_label)) {
                        $tpl->setCurrentBlock('input_label');
                        $tpl->setVariable('LABEL', $input->getTitle());
                        $tpl->setVariable('CONTENT', $input->render());
                        $first_label = false;
                    } else {
                        $tpl->setCurrentBlock('input');
                        $tpl->setVariable('CONTENT', $input->render());
                    }
                    break;
            }
            if ($this->isShowInfo()) {
                if ($this->isShowLabel()) {
                    $tpl->setCurrentBlock('input_info_label');
                    $tpl->setVariable('INFO_LABEL', $input->getInfo());
                    $tpl->parseCurrentBlock();
                } else {
                    $tpl->setCurrentBlock('input_info');
                    $tpl->setVariable('INFO', $input->getInfo());
                    $tpl->parseCurrentBlock();
                }
            }
            $tpl->parseCurrentBlock();
        }
        if ($this->getMulti() && !$this->getDisabled()) {
            $image_plus = xlvoGlyphGUI::get('plus');
            $show_remove = true;
            $is_removeable_hook = $this->getHook(self::HOOK_IS_LINE_REMOVABLE);
            if ($is_removeable_hook !== false && !$clean_render) {
                $show_remove = $is_removeable_hook($this->getValue());
            }
            $show_remove = true;
            $image_minus = ($show_remove) ? xlvoGlyphGUI::get('minus') : '<span class="glyphicon glyphicon-minus hide"></span>';
            $tpl->setCurrentBlock('multi_icons');
            $tpl->setVariable('IMAGE_PLUS', $image_plus);
            $tpl->setVariable('IMAGE_MINUS', $image_minus);
            $tpl->parseCurrentBlock();
            if ($this->isPositionMovable()) {
                $tpl->setCurrentBlock('multi_icons_move');
                $tpl->setVariable('IMAGE_UP', xlvoGlyphGUI::get(xlvoGlyphGUI::UP));
                $tpl->setVariable('IMAGE_DOWN', xlvoGlyphGUI::get(xlvoGlyphGUI::DOWN));
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }

    public function initCSSandJS(): void
    {
        global $tpl;
        $tpl->addJavascript('./Modules/OrgUnit/templates/default/multi_line_input.js');
    }

    public function insert(ilTemplate $a_tpl): int
    {
        $output = "";

        $output .= $this->render(0, true);
        if ($this->getMulti() && is_array($this->line_values) && count($this->line_values) > 0) {
            foreach ($this->line_values as $run => $data) {
                $object = $this;
                $object->setValue($data);
                $output .= $object->render($run);
            }
        } else {
            $output .= $this->render(0, true);
        }
        if ($this->getMulti()) {
            $output = '<div id="' . $this->getFieldId() . '" class="multi_line_input">' . $output
                . '</div>';
            $output .= '<script type="text/javascript">$("#' . $this->getFieldId()
                . '").multi_line_input(' . json_encode($this->input_options) . ')</script>';
        }
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $output);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Get HTML for table filter
     */
    public function getTableFilterHTML()
    {
        return $this->render();
    }

    /**
     * Get HTML for toolbar
     */
    public function getToolbarHTML(): string
    {
        return $this->render("toolbar");
    }

    public function isPositionMovable(): bool
    {
        return $this->position_movable;
    }

    public function setPositionMovable(bool $position_movable)
    {
        $this->position_movable = $position_movable;
    }

    public function isShowLabelOnce(): bool
    {
        return $this->show_label_once;
    }

    public function setShowLabelOnce(bool $show_label_once): void
    {
        $this->setShowLabel(false);
        $this->show_label_once = $show_label_once;
    }

    public function isShowInfo(): bool
    {
        return $this->show_info;
    }

    public function setShowInfo(bool $show_info): void
    {
        $this->show_info = $show_info;
    }
}
