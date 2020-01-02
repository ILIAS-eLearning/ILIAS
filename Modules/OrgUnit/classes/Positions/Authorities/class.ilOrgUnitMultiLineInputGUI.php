<?php

/**
 * Class ilOrgUnitMultiLineInputGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitMultiLineInputGUI extends \ilFormPropertyGUI
{
    const HOOK_IS_LINE_REMOVABLE = "hook_is_line_removable";
    const HOOK_IS_INPUT_DISABLED = "hook_is_disabled";
    const HOOK_BEFORE_INPUT_RENDER = "hook_before_render";
    /**
     * @var array
     */
    protected $cust_attr = array();
    /**
     * @var
     */
    protected $value;
    /**
     * @var array
     */
    protected $inputs = array();
    /**
     * @var array
     */
    protected $input_options = array();
    /**
     * @var array
     */
    protected $hooks = array();
    /**
     * @var array
     */
    protected $line_values = array();
    /**
     * @var string
     */
    protected $template_dir = '';
    /**
     * @var array
     */
    protected $post_var_cache = array();
    /**
     * @var bool
     */
    protected $show_label = false;
    /**
     * @var bool
     */
    protected $show_label_once = false;
    /**
     * @var array
     */
    protected $hidden_inputs = array();
    /**
     * @var bool
     */
    protected $position_movable = false;
    /**
     * @var int
     */
    protected $counter = 0;
    /**
     * @var bool
     */
    protected $show_info = false;


    /**
     * Constructor
     *
     * @param    string $a_title   Title
     * @param    string $a_postvar Post Variable
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType("line_select");
        $this->setMulti(true);
        $this->initCSSandJS();
    }


    /**
     * @return string
     */
    public function getHook($key)
    {
        if (isset($this->hooks[$key])) {
            return $this->hooks[$key];
        }

        return false;
    }


    /**
     * @param array $options
     */
    public function addHook($key, $options)
    {
        $this->hooks[$key] = $options;
    }


    /**
     * @param $key
     *
     * @return bool
     */
    public function removeHook($key)
    {
        if (isset($this->hooks[$key])) {
            unset($this->hooks[$key]);

            return true;
        }

        return false;
    }


    /**
     * @param       $input
     * @param array $options
     */
    public function addInput(\ilFormPropertyGUI $input, $options = array())
    {
        $this->inputs[$input->getPostVar()] = $input;
        $this->input_options[$input->getPostVar()] = $options;
        $this->counter++;
    }


    /**
     * @return mixed
     */
    public function getTemplateDir()
    {
        return $this->template_dir;
    }


    /**
     * @param mixed $template_dir
     */
    public function setTemplateDir($template_dir)
    {
        $this->template_dir = $template_dir;
    }


    /**
     * @return boolean
     */
    public function isShowLabel()
    {
        return $this->show_label;
    }


    /**
     * @param boolean $show_label
     */
    public function setShowLabel($show_label)
    {
        $this->show_label = $show_label;
    }


    /**
     * Get Options.
     *
     * @return    array    Options. Array ("value" => "option_text")
     */
    public function getInputs()
    {
        return $this->inputs;
    }


    /**
     * @param bool $a_multi
     */
    public function setMulti($a_multi, $a_sortable = false, $a_addremove = true)
    {
        $this->multi = $a_multi;
    }


    /**
     * Set Value.
     *
     * @param    string $a_value Value
     */
    public function setValue($a_value)
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


    /**
     * Get Value.
     *
     * @return    string    Value
     */
    public function getValue()
    {
        $out = array();
        foreach ($this->inputs as $key => $item) {
            $out[$key] = $item->getValue();
        }

        return $out;
    }


    /**
     * Set value by array
     *
     * @param    array $a_values value array
     */
    public function setValueByArray($a_values)
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
     *
     * @return    boolean        Input ok, true/false
     */
    public function checkInput()
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


    /**
     * @param            $key
     * @param            $value
     * @param bool|false $override
     */
    public function addCustomAttribute($key, $value, $override = false)
    {
        if (isset($this->cust_attr[$key]) && !$override) {
            $this->cust_attr[$key] .= ' ' . $value;
        } else {
            $this->cust_attr[$key] = $value;
        }
    }


    /**
     * @return array
     */
    public function getCustomAttributes()
    {
        return (array) $this->cust_attr;
    }


    /**
     * @param                    $iterator_id
     * @param \ilFormPropertyGUI $input
     *
     * @return string
     */
    protected function createInputPostVar($iterator_id, \ilFormPropertyGUI $input)
    {
        if ($this->getMulti()) {
            return $this->getPostVar() . '[' . $iterator_id . '][' . $input->getPostVar() . ']';
        } else {
            return $this->getPostVar() . '[' . $input->getPostVar() . ']';
        }
    }


    /**
     * Render item
     *
     * @param int $iterator_id
     *
     * @return string
     * @throws \ilException
     */
    public function render($iterator_id = 0, $clean_render = false)
    {
        $first_label = true;
        $tpl = new \ilTemplate("tpl.multi_line_input.html", true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting');
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
                    $tpl->setVariable('VALUE', \ilUtil::prepareFormOutput($input->getValue()));
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


    public function initCSSandJS()
    {
        global $tpl;
        $tpl->addJavascript('./Modules/OrgUnit/templates/default/multi_line_input.js');
    }


    /**
     * Insert property html
     *
     * @return    int    Size
     */
    public function insert(&$a_tpl)
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
        $html = $this->render();

        return $html;
    }


    /**
     * Get HTML for toolbar
     */
    public function getToolbarHTML()
    {
        $html = $this->render("toolbar");

        return $html;
    }


    /**
     * @return boolean
     */
    public function isPositionMovable()
    {
        return $this->position_movable;
    }


    /**
     * @param boolean $position_movable
     */
    public function setPositionMovable($position_movable)
    {
        $this->position_movable = $position_movable;
    }


    /**
     * @return boolean
     */
    public function isShowLabelOnce()
    {
        return $this->show_label_once;
    }


    /**
     * @param boolean $show_label_once
     */
    public function setShowLabelOnce($show_label_once)
    {
        $this->setShowLabel(false);
        $this->show_label_once = $show_label_once;
    }


    /**
     * @return boolean
     */
    public function isShowInfo()
    {
        return $this->show_info;
    }


    /**
     * @param boolean $show_info
     */
    public function setShowInfo($show_info)
    {
        $this->show_info = $show_info;
    }
}
