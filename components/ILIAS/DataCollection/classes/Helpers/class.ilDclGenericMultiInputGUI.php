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

class ilDclGenericMultiInputGUI extends ilFormPropertyGUI
{
    public const HOOK_IS_LINE_REMOVABLE = "hook_is_line_removable";
    public const HOOK_IS_INPUT_DISABLED = "hook_is_disabled";
    public const HOOK_BEFORE_INPUT_RENDER = "hook_before_render";
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $renderer;

    protected array $cust_attr = [];
    protected array $value = [];
    protected array $inputs = [];
    protected array $input_options = [];
    protected array $hooks = [];
    protected ?array $line_values = [];
    protected string $template_dir = '';
    protected array $post_var_cache = [];
    protected bool $show_label = false;
    protected int $limit = 999999;
    protected bool $allow_empty_fields = false;

    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        parent::__construct($a_title, $a_postvar);

        $this->setType("line_select");
        $this->setMulti(true);
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function isAllowEmptyFields(): bool
    {
        return $this->allow_empty_fields;
    }

    public function setAllowEmptyFields(bool $allow_empty_fields): void
    {
        $this->allow_empty_fields = $allow_empty_fields;
    }

    /**
     * @return string|bool
     */
    public function getHook(string $key)
    {
        if (isset($this->hooks[$key])) {
            return $this->hooks[$key];
        }

        return false;
    }

    public function addHook(string $key, array $options)
    {
        $this->hooks[$key] = $options;
    }

    public function removeHook(string $key): bool
    {
        if (isset($this->hooks[$key])) {
            unset($this->hooks[$key]);

            return true;
        }

        return false;
    }

    public function addInput(ilFormPropertyGUI $input, array $options = [])
    {
        $input->setRequired(!$this->allow_empty_fields);
        $this->inputs[$input->getPostVar()] = $input;
        $this->input_options[$input->getPostVar()] = $options;
    }

    public function isShowLabel(): bool
    {
        return $this->show_label;
    }

    public function setShowLabel(bool $show_label): void
    {
        $this->show_label = $show_label;
    }

    /**
     * Get Options.
     * @return    array    Options. Array ("value" => "option_text")
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function setMulti(
        bool $a_multi,
        bool $a_sortable = false,
        bool $a_addremove = true
    ): void {
        $this->multi = $a_multi;
        $this->multi_sortable = $a_sortable;
    }

    /**
     * Set Value.
     */
    public function setValue(array $value): void
    {
        $this->value = $value;

        foreach ($this->inputs as $key => $item) {
            if (array_key_exists($key, $value)) {
                if ($item instanceof ilCheckboxInputGUI) {
                    $item->setChecked((bool) $value[$key]);
                } else {
                    if ($item instanceof ilDateTimeInputGUI) {
                        if (ilCalendarUtil::parseIncomingDate($value[$key])) {
                            $item->setDate(new ilDate($value[$key], IL_CAL_DATE));
                        } else {
                            $item->setDate();
                        }
                    } else {
                        $item->setValue($value[$key]);
                    }
                }
            }
        }
    }

    /**
     * Get Value.
     */
    public function getValue(): array
    {
        $out = [];
        foreach ($this->inputs as $key => $item) {
            $out[$key] = $item->getValue();
        }

        return $out;
    }

    /**
     * Set value by array
     */
    public function setValueByArray(array $a_values): void
    {
        $data = $a_values[$this->getPostVar()] ?? [];
        if ($this->getMulti()) {
            $this->line_values = $data;
        } else {
            $this->setValue($data);
        }
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     */
    public function checkInput(): bool
    {
        global $lng;

        $valid = true;

        $value = $this->arrayArray($this->getPostVar());
        // escape data
        $out_array = [];
        foreach ($value as $item_num => $item) {
            foreach ($this->inputs as $input_key => $input) {
                if (isset($item[$input_key])) {
                    if ($input instanceof ilDateTimeInputGUI) {
                        $out = (is_string($item[$input_key])) ? ilUtil::stripSlashes($item[$input_key]) : $item[$input_key];
                        if (ilCalendarUtil::parseIncomingDate($out)) {
                            $out_array[$item_num][$input_key] = $out;
                        } else {
                            $valid = false;
                            $this->setAlert($this->lng->txt("form_msg_wrong_date"));
                            $out_array[$item_num][$input_key] = null;
                        }
                    }
                }
            }
        }

        $this->setValue($out_array);

        if ($this->getRequired() && !trim(implode("", $this->getValue()))) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            $valid = false;
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
        return $this->cust_attr;
    }

    protected function createInputPostVar(string $iterator_id, ilFormPropertyGUI $input): string
    {
        if ($this->getMulti()) {
            return $this->getPostVar() . '[' . $iterator_id . '][' . $input->getPostVar() . ']';
        } else {
            return $this->getPostVar() . '[' . $input->getPostVar() . ']';
        }
    }

    /**
     * Render item
     * @throws ilTemplateException
     */
    public function render(string $iterator_id = "0", bool $clean_render = false): string
    {
        $tpl = new ilTemplate("tpl.prop_generic_multi_line.html", true, true, 'components/ILIAS/DataCollection');

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
            if (!method_exists($input, 'render')) {
                throw new ilException(
                    "Method " . get_class($input)
                    . "::render() does not exists! You cannot use this input-type in ilMultiLineInputGUI"
                );
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

            //var_dump($input);

            if ($this->isShowLabel()) {
                $tpl->setCurrentBlock('input_label');
                $tpl->setVariable('LABEL', $input->getTitle());
            } else {
                $tpl->setCurrentBlock('input');
            }
            $tpl->setVariable('CONTENT', $input->render());
            $tpl->parseCurrentBlock();
        }

        if ($this->getMulti() && !$this->getDisabled()) {
            $show_remove = true;
            $is_removeable_hook = $this->getHook(self::HOOK_IS_LINE_REMOVABLE);
            if ($is_removeable_hook !== false && !$clean_render) {
                $show_remove = $is_removeable_hook($this->getValue());
            }
            $tpl->setCurrentBlock('multi_icons');
            $tpl->setVariable('IMAGE_PLUS', $this->renderer->render($this->ui_factory->symbol()->glyph()->add()));
            if ($show_remove) {
                $tpl->setVariable('IMAGE_MINUS', $this->renderer->render($this->ui_factory->symbol()->glyph()->remove()));
            } else {
                $tpl->setVariable('IMAGE_MINUS', '<span class="glyphicon glyphicon-minus hide"></span>');
            }
            if ($this->multi_sortable) {
                $tpl->setVariable('IMAGE_UP', $this->renderer->render($this->ui_factory->symbol()->glyph()->up()));
                $tpl->setVariable('IMAGE_DOWN', $this->renderer->render($this->ui_factory->symbol()->glyph()->down()));
            }
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * Insert property html
     */
    public function insert(ilTemplate $a_tpl): void
    {
        $output = $this->render("0", true);

        if ($this->getMulti() && is_array($this->line_values) && count($this->line_values) > 0) {
            $counter = 0;
            foreach ($this->line_values as $i => $data) {
                $object = $this;
                $object->setValue($data);
                $output .= $object->render((string)$i);
                $counter++;
            }
        } else {
            $output .= $this->render("1", true);
        }

        if ($this->getMulti()) {
            $output = '<div id="' . $this->getFieldId() . '" class="multi_line_input">' . $output . '</div>';
            $this->global_tpl->addJavaScript('assets/js/generic_multi_line_input.js');
            $id = $this->getFieldId();
            $element_config = json_encode($this->input_options);
            $options = json_encode(['limit' => $this->limit,
                                        'sortable' => $this->multi_sortable,
                                        'locale' => $this->lng->getLangKey()]);
            $this->global_tpl->addOnLoadCode("il.DataCollection.genericMultiLineInit('$id',$element_config,$options);");
        }

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $output);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Get HTML for table filter
     */
    public function getTableFilterHTML(): string
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

    public function getSubItems(): array
    {
        return [];
    }
}
