<?php
require_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
 * Class ilDclGenericMultiInputGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclGenericMultiInputGUI extends ilFormPropertyGUI
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
     * @var
     */
    protected $limit = 0;
    /**
     * @var bool
     */
    protected $allow_empty_fields = false;


    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }


    /**
     * set a limit of possible lines, 0 = no limit
     *
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }


    /**
     * @return boolean
     */
    public function isAllowEmptyFields()
    {
        return $this->allow_empty_fields;
    }


    /**
     * @param boolean $allow_empty_fields
     */
    public function setAllowEmptyFields($allow_empty_fields)
    {
        $this->allow_empty_fields = $allow_empty_fields;
    }


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
    public function addInput(ilFormPropertyGUI $input, $options = array())
    {
        $input->setRequired(!$this->allow_empty_fields);
        $this->inputs[$input->getPostVar()] = $input;
        $this->input_options[$input->getPostVar()] = $options;
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
        $this->multi_sortable = $a_sortable;
    }


    /**
     * Set Value.
     *
     * @param    string $a_value Value
     */
    public function setValue($a_value)
    {
        foreach ($this->inputs as $key => $item) {
            if ($item instanceof ilCheckboxInputGUI) {
                $item->setChecked((bool) $a_value[$key]);
            } else {
                if ($item instanceof ilDateTimeInputGUI) {
                    $item->setDate(new ilDate($a_value[$key], IL_CAL_DATE));
                } else {
                    if (method_exists($item, 'setValue')) {
                        $item->setValue($a_value[$key]);
                    }
                }
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
                    $out_array[$item_num][$input_key] = (is_string($item[$input_key])) ? ilUtil::stripSlashes($item[$input_key]) : $item[$input_key];
                }
            }
        }
        $_POST[$this->getPostVar()] = $out_array;

        if ($this->getRequired() && !trim(implode("", $_POST[$this->getPostVar()]))) {
            $valid = false;
        }

        // validate
        foreach ($this->inputs as $input_key => $inputs) {
            foreach ($out_array as $subitem) {
                $_POST[$inputs->getPostVar()] = $subitem[$inputs->getPostVar()];
                if (!$inputs->checkInput()) {
                    $valid = false;
                }
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
     * @param                   $iterator_id
     * @param ilFormPropertyGUI $input
     *
     * @return string
     */
    protected function createInputPostVar($iterator_id, ilFormPropertyGUI $input)
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
     * @throws ilException
     */
    public function render($iterator_id = 0, $clean_render = false)
    {
        $tpl = new ilTemplate("tpl.prop_generic_multi_line.html", true, true, 'Modules/DataCollection');

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
                $tpl->setVariable('CONTENT', $input->render());
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setCurrentBlock('input');
                $tpl->setVariable('CONTENT', $input->render());
                $tpl->parseCurrentBlock();
            }
        }

        if ($this->getMulti() && !$this->getDisabled()) {
            $tpl->setVariable('IMAGE_MINUS', ilGlyphGUI::get(ilGlyphGUI::REMOVE));

            $show_remove = true;
            $is_removeable_hook = $this->getHook(self::HOOK_IS_LINE_REMOVABLE);
            if ($is_removeable_hook !== false && !$clean_render) {
                $show_remove = $is_removeable_hook($this->getValue());
            }

            $image_minus = ($show_remove) ? ilGlyphGUI::get(ilGlyphGUI::REMOVE) : '<span class="glyphicon glyphicon-minus hide"></span>';
            $tpl->setCurrentBlock('multi_icons');
            $tpl->setVariable('IMAGE_PLUS', ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable('IMAGE_MINUS', $image_minus);
            if ($this->multi_sortable) {
                $tpl->setVariable('IMAGE_UP', ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable('IMAGE_DOWN', ilGlyphGUI::get(ilGlyphGUI::DOWN));
            }
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }


    /**
     * Insert property html
     *
     * @return    int    Size
     */
    public function insert(&$a_tpl)
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        $output = "";
        //		$tpl->addCss($this->getTemplateDir() . '/templates/css/multi_line_input.css');

        $output .= $this->render(0, true);

        if ($this->getMulti() && is_array($this->line_values) && count($this->line_values) > 0) {
            $counter = 0;
            foreach ($this->line_values as $i => $data) {
                $object = $this;
                $object->setValue($data);
                $output .= $object->render($i);
                $counter++;
            }
        } else {
            $output .= $this->render(1, true);
        }

        if ($this->getMulti()) {
            $output = '<div id="' . $this->getFieldId() . '" class="multi_line_input">' . $output . '</div>';
            $tpl->addJavascript('Modules/DataCollection/js/generic_multi_line_input.js');
            $output .= '<script type="text/javascript">$("#' . $this->getFieldId() . '").multi_line_input('
                . json_encode($this->input_options) . ', '
                . json_encode(array('limit' => $this->limit, 'sortable' => $this->multi_sortable, 'locale' => $DIC->language()->getLangKey()))
                . ')</script>';
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


    //	/**
    //	 * @param bool|false $a_sortable
    //	 *
    //	 * @return string
    //	 */
    //	public function getMultiIconsHTML($a_sortable = false) {
    //
    //		$id = $this->getFieldId();
    //
    //		if (file_exists(ilUtil::getImagePath('edit_add.png'))) {
    //			$html = '<a href="#" style="display: inline-block;" class="add_button"><img src="' . ilUtil::getImagePath('edit_add.png') . '" /></a>';
    //			$html .= '<a href="#" style="display: inline-block;" class="remove_button"><img src="' . ilUtil::getImagePath('edit_remove.png')
    //				. '" /></a>';
    //		} else {
    //			$html = '<a href="#" style="display: inline-block;" class="add_button"><span class="sr-only"></span><span class="glyphicon glyphicon-plus"></span></a>';
    //			$html .= '<a href="#" style="display: inline-block;" class="remove_button"><span class="sr-only"></span><span class="glyphicon glyphicon-minus"></span></a>';
    //		}
    //
    //		/*if($a_sortable)
    //		{
    //			$html .= '&nbsp;<input align="absmiddle" type="image" id="ilMultiDwn~'.$id.'~0"'.
    //				' src="'.ilUtil::getImagePath('icon_down_s.png').'" alt="'.
    //				$lng->txt("down").'" title="'.$lng->txt("down").'" onclick="javascript: return false;" />'.
    //				'<input align="absmiddle" type="image" id="ilMultiUp~'.$id.'~0"'.
    //				' src="'.ilUtil::getImagePath('icon_up_s.png').'" alt="'.$lng->txt("up").
    //				'" title="'.$lng->txt("up").'"  onclick="javascript: return false;" />';
    //		}*/
    //
    //		return $html;
    //	}

    public function getSubItems()
    {
        return array();
    }
}
