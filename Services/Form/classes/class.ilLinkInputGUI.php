<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a external and/or internal link in a property form.
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilLinkInputGUI: ilFormPropertyDispatchGUI
* @ilCtrl_Calls ilLinkInputGUI: ilInternalLinkGUI
*
* @ingroup	ServicesForm
*/
class ilLinkInputGUI extends ilFormPropertyGUI
{
    const EXTERNAL_LINK_MAX_LENGTH = 200;

    const BOTH = "both";
    const INT = "int";
    const EXT = "ext";
    protected $allowed_link_types = self::BOTH;
    protected $int_link_default_type = "RepositoryItem";
    protected $int_link_default_obj = 0;
    protected $int_link_filter_types = array("RepositoryItem");
    protected $filter_white_list = true;

    protected $external_link_max_length = self::EXTERNAL_LINK_MAX_LENGTH;

    protected static $iltypemap = array(
        "page" => "PageObject",
        "chap" => "StructureObject",
        "term" => "GlossaryItem",
        "wpage" => "WikiPage"
    );

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();

        parent::__construct($a_title, $a_postvar);
        $this->setType("link");

        $this->obj_definition = $DIC["objDefinition"];
    }
    
    /**
     * Set allowed link types (BOTH, INT, EXT)
     *
     * @param string $a_val self::BOTH|self::INT|self::EXT
     */
    public function setAllowedLinkTypes($a_val)
    {
        $this->allowed_link_types = $a_val;
    }
    
    /**
     * Get allowed link types (BOTH, INT, EXT)
     *
     * @return string self::BOTH|self::INT|self::EXT
     */
    public function getAllowedLinkTypes()
    {
        return $this->allowed_link_types;
    }
    
    /**
     * Set internal link default
     *
     * @param string $a_type link type
     * @param int $a_obj object id
     */
    public function setInternalLinkDefault($a_type, $a_obj = 0)
    {
        $this->int_link_default_type = $a_type;
        $this->int_link_default_obj = $a_obj;
    }
    
    /**
     * Set internal link filter types
     *
     * @param array $a_val filter types
     */
    public function setInternalLinkFilterTypes(array $a_val)
    {
        $this->int_link_filter_types = $a_val;
    }

    /**
     * Get internal types to xml attribute types map
     *
     * @return string[]
     */
    public static function getTypeToAttrType()
    {
        return self::$iltypemap;
    }

    /**
     * Get internal types to xml attribute types map (reverse)
     *
     * @return string[]
     */
    public static function getAttrTypeToType()
    {
        return array_flip(self::$iltypemap);
    }

    /**
     * Set filter white list
     *
     * @param bool $a_val filter list is white list
     */
    public function setFilterWhiteList($a_val)
    {
        $this->filter_white_list = $a_val;
    }
    
    /**
     * Get filter white list
     *
     * @return bool filter list is white list
     */
    public function getFilterWhiteList()
    {
        return $this->filter_white_list;
    }

    /**
     * @param int max length for external links
     */
    public function setExternalLinkMaxLength($a_max)
    {
        $this->external_link_max_length = $a_max;
    }

    /**
     * @return int
     */
    public function getExternalLinkMaxLength()
    {
        return $this->external_link_max_length;
    }

    /**
    * Execute current command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            case "ilinternallinkgui":
                $lng->loadLanguageModule("content");
                require_once("./Services/Link/classes/class.ilInternalLinkGUI.php");
                $link_gui = new ilInternalLinkGUI(
                    $this->int_link_default_type,
                    $this->int_link_default_obj
                );
                foreach ($this->int_link_filter_types as $t) {
                    $link_gui->filterLinkType($t);
                }
                $link_gui->setFilterWhiteList($this->getFilterWhiteList());
                $link_gui->setMode("asynch");

                $ret = $ilCtrl->forwardCommand($link_gui);
                break;

            default:
                var_dump($cmd);
                //exit();
        }
        
        return $ret;
    }
    
    /**
     * Set Value.
     *
     * @param	string	$a_value	Value
     */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
     * Get Value.
     *
     * @return	string	Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        switch ($a_values[$this->getPostVar() . "_mode"]) {
            case "int":
                if ($a_values[$this->getPostVar() . "_ajax_type"] &&
                    $a_values[$this->getPostVar() . "_ajax_id"]) {
                    $val = $a_values[$this->getPostVar() . "_ajax_type"] . "|" .
                        $a_values[$this->getPostVar() . "_ajax_id"];
                    if ($a_values[$this->getPostVar() . "_ajax_target"] != "") {
                        $val .= "|" . $a_values[$this->getPostVar() . "_ajax_target"];
                    }
                    $this->setValue($val);
                }
                break;

            case "no":
                break;

            default:
                if ($a_values[$this->getPostVar()]) {
                    $this->setValue($a_values[$this->getPostVar()]);
                }
                break;
        }
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        // debugging
        // return false;
        
        if ($this->getRequired()) {
            switch ($_POST[$this->getPostVar() . "_mode"]) {
                case "ext":
                    if (!$_POST[$this->getPostVar()]) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                    break;
                    
                case "int":
                    if (!$_POST[$this->getPostVar() . "_ajax_type"] ||
                        !$_POST[$this->getPostVar() . "_ajax_id"]) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                    break;

                case "no":
                default:
                    $this->setAlert($lng->txt("msg_input_is_required"));
                    return false;
            }
        }
        
        if ($_POST[$this->getPostVar() . "_mode"] == "int") {
            $_POST[$this->getPostVar() . "_ajax_type"] = ilUtil::stripSlashes($_POST[$this->getPostVar() . "_ajax_type"]);
            $_POST[$this->getPostVar() . "_ajax_id"] = ilUtil::stripSlashes($_POST[$this->getPostVar() . "_ajax_id"]);
            $_POST[$this->getPostVar() . "_ajax_target"] = ilUtil::stripSlashes($_POST[$this->getPostVar() . "_ajax_target"]);
            
            // overwriting post-data so getInput() will work
            $val = $_POST[$this->getPostVar() . "_ajax_type"] . "|" .
                $_POST[$this->getPostVar() . "_ajax_id"];
            if ($_POST[$this->getPostVar() . "_ajax_target"] != "") {
                $val .= "|" . $_POST[$this->getPostVar() . "_ajax_target"];
            }

            $_POST[$this->getPostVar()] = $val;
        } elseif ($_POST[$this->getPostVar() . "_mode"] == "no") {
            $_POST[$this->getPostVar()] = "";
        } else {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        }
        
        return true;
    }

    /**
    * Render item
    */
    public function render()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        // parse settings
        $has_int = $has_ext = $has_radio = false;
        switch ($this->getAllowedLinkTypes()) {
            case self::EXT:
                $has_ext = true;
                break;
            
            case self::INT:
                $has_int = true;
                break;
            
            case self::BOTH:
                $has_int = true;
                $has_ext = true;
                $has_radio = true;
                break;
        }
        if (!$this->getRequired()) {
            // see #0021274
//			$has_radio = true;
        }
        
        // external
        if ($has_ext) {
            $title = $has_radio ? $lng->txt("url") : "";
            
            // external
            $ti = new ilTextInputGUI($title, $this->getPostVar());
            $ti->setMaxLength($this->getExternalLinkMaxLength());
        }
        
        // internal
        if ($has_int) {
            $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->getPostVar());
            $link = array(get_class($this->getParent()), "ilformpropertydispatchgui", get_class($this), "ilinternallinkgui");
            $link = $ilCtrl->getLinkTargetByClass($link, "", false, true, false);
            $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $_REQUEST["postvar"]);
                                
            $no_disp_class = (strpos($this->getValue(), "|"))
                ? ""
                : " ilNoDisplay";
            
            $itpl = new ilTemplate('tpl.prop_link.html', true, true, 'Services/Form');
            $itpl->setVariable("VAL_ID", $this->getPostVar());
            $itpl->setVariable("URL_EDIT", $link);
            $itpl->setVariable("TXT_EDIT", $lng->txt("form_get_link"));
            $itpl->setVariable("CSS_REMOVE", $no_disp_class);
            $itpl->setVariable("TXT_REMOVE", $lng->txt("remove"));
                        
            $ne = new ilNonEditableValueGUI($lng->txt("object"), $this->getPostVar() . "_val", true);
                        
            // hidden field for selected value
            $hidden_type = new ilHiddenInputGUI($this->getPostVar() . "_ajax_type");
            $hidden_id = new ilHiddenInputGUI($this->getPostVar() . "_ajax_id");
            $hidden_target = new ilHiddenInputGUI($this->getPostVar() . "_ajax_target");
        }
        
        // mode
        if ($has_radio) {
            $ext = new ilRadioOption($lng->txt("form_link_external"), "ext");
            $ext->addSubItem($ti);
            
            $int = new ilRadioOption($lng->txt("form_link_internal"), "int");
            $int->addSubItem($ne);
            
            $mode = new ilRadioGroupInputGUI("", $this->getPostVar() . "_mode");
            if (!$this->getRequired()) {
                $no = new ilRadioOption($lng->txt("form_no_link"), "no");
                $mode->addOption($no);
            }
            $mode->addOption($ext);
            $mode->addOption($int);
        } else {
            $mode = new ilHiddenInputGUI($this->getPostVar() . "_mode");
            if ($has_int) {
                $mode->setValue("int");
            } else {
                $mode->setValue("ext");
            }
        }

        // value
        $value = $this->getValue();
        if ($value) {
            // #15647
            if ($has_int && self::isInternalLink($value)) {
                $mode->setValue("int");
                                
                $value_trans = self::getTranslatedValue($value);
                
                $value = explode("|", $value);
                $hidden_type->setValue($value[0]);
                $hidden_id->setValue($value[1]);
                $hidden_target->setValue($value[2]);
                
                $itpl->setVariable("VAL_OBJECT_TYPE", $value_trans["type"]);
                $itpl->setVariable("VAL_OBJECT_NAME", $value_trans["name"]);
                if ($value[2] != "") {
                    $itpl->setVariable("VAL_TARGET_FRAME", "(" . $value[2] . ")");
                }
            } elseif ($has_ext) {
                $mode->setValue("ext");
                
                $ti->setValue($value);
            }
        } elseif (!$this->getRequired()) {
            $mode->setValue("no");
        }
        
        // #10185 - default for external urls
        if ($has_ext && !$ti->getValue()) {
            $ti->setValue("http://");
        }
        
        $ne->setValue($itpl->get());
            
        // to html
        if ($has_radio) {
            $html = $mode->render();
        } else {
            $html = $mode->getToolbarHTML();
            
            if ($has_ext) {
                $html .= $ti->getToolbarHTML();
            } else {
                $html .= $ne->render() .
                    '<div class="help-block">' . $ne->getInfo() . '</div>';
            }
        }

        // js for internal link
        if ($has_int) {
            include_once("./Services/Link/classes/class.ilInternalLinkGUI.php");
            $html .= $hidden_type->getToolbarHTML() .
                $hidden_id->getToolbarHTML() .
                $hidden_target->getToolbarHTML();
        }
        
        return $html;
    }
    
    public function getContentOutsideFormTag()
    {
        if ($this->getAllowedLinkTypes() == self::INT ||
            $this->getAllowedLinkTypes() == self::BOTH) {
            // as the ajax-panel uses a form it has to be outside of the parent form!
            return ilInternalLinkGUI::getInitHTML("");
        }
    }
    
    public static function isInternalLink($a_value)
    {
        if (strpos($a_value, "|")) {
            $parts = explode("|", $a_value);
            if (sizeof($parts) == 2 || sizeof($parts) == 3) {
                // numeric id
                if (is_numeric($parts[1])) {
                    // simple type
                    if (preg_match("/^[a-zA-Z_]+$/", $parts[0], $matches)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    public static function getTranslatedValue($a_value)
    {
        global $DIC;

        $lng = $DIC->language();
        
        $value = explode("|", $a_value);
        
        switch ($value[0]) {
            case "media":
                $type = $lng->txt("obj_mob");
                $name = ilObject::_lookupTitle($value[1]);
                break;

            case "page":
                include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
                $type = $lng->txt("obj_pg");
                $name = ilLMPageObject::_lookupTitle($value[1]);
                break;

            case "chap":
                include_once("./Modules/LearningModule/classes/class.ilStructureObject.php");
                $type = $lng->txt("obj_st");
                $name = ilStructureObject::_lookupTitle($value[1]);
                break;

            case "term":
                include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
                $type = $lng->txt("term");
                $name = ilGlossaryTerm::_lookGlossaryTerm($value[1]);
                break;

            default:
                $type = $lng->txt("obj_" . $value[0]);
                $name = ilObject::_lookupTitle(ilObject::_lookupObjId($value[1]));
                break;
        }
        
        return array("type" => $type, "name" => $name);
    }

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Get value as internal link attributes
     *
     * @return array (with keys "Type", "Target" and "TargetFrame")
     */
    public function getIntLinkAttributes()
    {
        $val = explode("|", $_POST[$this->getPostVar()]);

        $ret = false;
        $type = "";
        $target = "";
        if (self::isInternalLink($_POST[$this->getPostVar()])) {
            $target_frame = $val[2];
            $map = self::getTypeToAttrType();
            if (isset($map[$val[0]])) {
                $type = $map[$val[0]];
                $target_type = $val[0];
                if ($val[0] == "chap") {
                    $target_type = "st";
                }
                if ($val[0] == "term") {
                    $target_type = "git";
                }
                if ($val[0] == "page") {
                    $target_type = "pg";
                }
                $target = "il__" . $target_type . "_" . $val[1];
            } elseif ($this->obj_definition->isRBACObject($val[0])) {
                $type = "RepositoryItem";
                $target = "il__obj_" . $val[1];
            }
            if ($type != "") {
                $ret = array(
                    "Target" => $target,
                    "Type" => $type,
                    "TargetFrame" => $target_frame
                );
            }
        }
        return $ret;
    }
    
    /**
     * Set value by internal links attributes
     *
     * @param
     * @return
     */
    public function setValueByIntLinkAttributes($a_type, $a_target, $a_target_frame = "")
    {
        $t = explode("_", $a_target);
        $target_id = $t[3];
        $type = "";
        $map = self::getAttrTypeToType();
        if ($a_type == "RepositoryItem") {
            $type = ilObject::_lookupType($target_id, true);
        } elseif (isset($map[$a_type])) {
            $type = $map[$a_type];
        }
        if ($type != "" && $target_id != "") {
            $val = $type . "|" . $target_id;
            if ($a_target_frame != "") {
                $val .= "|" . $a_target_frame;
            }
            $this->setValue($val);
        }
    }
}
