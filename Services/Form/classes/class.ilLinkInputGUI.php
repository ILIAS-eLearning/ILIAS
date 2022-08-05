<?php declare(strict_types=1);

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
 * This class represents a external and/or internal link in a property form.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_IsCalledBy ilLinkInputGUI: ilFormPropertyDispatchGUI
 * @ilCtrl_Calls ilLinkInputGUI: ilInternalLinkGUI
 */
class ilLinkInputGUI extends ilFormPropertyGUI
{
    public const EXTERNAL_LINK_MAX_LENGTH = 200;
    public const LIST = "list";
    public const BOTH = "both";
    public const INT = "int";
    public const EXT = "ext";

    protected string $allowed_link_types = self::BOTH;
    protected string $int_link_default_type = "RepositoryItem";
    protected int $int_link_default_obj = 0;
    protected array $int_link_filter_types = array("RepositoryItem");
    protected bool $filter_white_list = true;
    protected int $external_link_max_length = self::EXTERNAL_LINK_MAX_LENGTH;

    protected static array $iltypemap = array(
        "page" => "PageObject",
        "chap" => "StructureObject",
        "term" => "GlossaryItem",
        "wpage" => "WikiPage"
    );
    protected ilObjectDefinition $obj_definition;
    protected string $requested_postvar;
    protected string $value = "";

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();

        parent::__construct($a_title, $a_postvar);
        $this->setType("link");

        $this->obj_definition = $DIC["objDefinition"];

        $this->requested_postvar = $this->str("postvar");
    }
    
    /**
     * Set allowed link types (LIST, BOTH, INT, EXT)
     *
     * @param string $a_val self::LIST|self::BOTH|self::INT|self::EXT
     */
    public function setAllowedLinkTypes(string $a_val) : void
    {
        $this->allowed_link_types = $a_val;
    }
    
    public function getAllowedLinkTypes() : string
    {
        return $this->allowed_link_types;
    }
    
    /**
     * Set internal link default
     *
     * @param string $a_type link type
     * @param int $a_obj object id
     */
    public function setInternalLinkDefault(
        string $a_type,
        int $a_obj = 0
    ) : void {
        $this->int_link_default_type = $a_type;
        $this->int_link_default_obj = $a_obj;
    }
    
    /**
     * Set internal link filter types
     *
     * @param array $a_val filter types
     */
    public function setInternalLinkFilterTypes(array $a_val) : void
    {
        $this->int_link_filter_types = $a_val;
    }

    /**
     * Get internal types to xml attribute types map
     *
     * @return string[]
     */
    public static function getTypeToAttrType() : array
    {
        return self::$iltypemap;
    }

    /**
     * Get internal types to xml attribute types map (reverse)
     *
     * @return string[]
     */
    public static function getAttrTypeToType() : array
    {
        return array_flip(self::$iltypemap);
    }

    /**
     * Set filter white list
     *
     * @param bool $a_val filter list is white list
     */
    public function setFilterWhiteList(bool $a_val) : void
    {
        $this->filter_white_list = $a_val;
    }
    
    public function getFilterWhiteList() : bool
    {
        return $this->filter_white_list;
    }

    /**
     * @param int max length for external links
     */
    public function setExternalLinkMaxLength(int $a_max) : void
    {
        $this->external_link_max_length = $a_max;
    }

    public function getExternalLinkMaxLength() : int
    {
        return $this->external_link_max_length;
    }

    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        $ret = "";
        switch ($next_class) {
            case "ilinternallinkgui":
                $lng->loadLanguageModule("content");
                $link_gui = new ilInternalLinkGUI(
                    $this->int_link_default_type,
                    $this->int_link_default_obj
                );
                foreach ($this->int_link_filter_types as $t) {
                    $link_gui->filterLinkType($t);
                }
                $link_gui->setFilterWhiteList($this->getFilterWhiteList());

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
     * @param string $a_value
     */
    public function setValue(string $a_value) : void
    {
        $this->value = $a_value;
    }

    /**
     * Get Value.
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }

    public function setValueByArray(array $a_values) : void
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
    * @return    bool        Input ok, true/false
    */
    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        // debugging
        // return false;

        $mode_type = $this->str($this->getPostVar() . "_mode_type");
        $ajax_type = $this->str($this->getPostVar() . "_ajax_type");
        $ajax_id = $this->str($this->getPostVar() . "_ajax_id");
        $mode = $this->str($this->getPostVar() . "_mode");
        $value = $this->str($this->getPostVar());

        if ($this->getRequired()) {
            if ($mode_type == "list") {
                return true;
            }

            switch ($mode) {
                case "ext":
                    if (!$value) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                    break;
                    
                case "int":
                    if (!$ajax_type || !$ajax_id) {
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

        return true;
    }

    public function getInput() : string
    {
        $ajax_type = $this->str($this->getPostVar() . "_ajax_type");
        $ajax_id = $this->str($this->getPostVar() . "_ajax_id");
        $ajax_target = $this->str($this->getPostVar() . "_ajax_target");
        $mode = $this->str($this->getPostVar() . "_mode");
        $value = $this->str($this->getPostVar());

        if ($mode == "int") {
            // overwriting post-data so getInput() will work
            $val = $ajax_type . "|" . $ajax_id;
            if ($ajax_target != "") {
                $val .= "|" . $ajax_target;
            }
            return $val;
        } elseif ($mode == "no") {
            return "";
        }
        return $value;
    }

    public function render() : string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ti = null;
        $ne = null;
        $hidden_type = null;
        $hidden_id = null;
        $hidden_target = null;

        // parse settings
        $has_int = $has_ext = $has_radio = $has_list = false;
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

            case self::LIST:
                $has_int = true;
                $has_ext = true;
                $has_radio = true;
                $has_list = true;
                break;
        }
        if (!$this->getRequired()) {
            // see #0021274
            $has_radio = true;
        }
        
        // external
        if ($has_ext) {
            $title = $has_radio ? $lng->txt("url") : "";
            
            // external
            $ti = new ilTextInputGUI($title, $this->getPostVar());
            $ti->setMaxLength($this->getExternalLinkMaxLength());
        }

        $itpl = new ilTemplate('tpl.prop_link.html', true, true, 'Services/Form');

        // internal
        if ($has_int) {
            $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->getPostVar());
            $link = array(get_class($this->getParentForm()), "ilformpropertydispatchgui", get_class($this), "ilinternallinkgui");
            $link = $ilCtrl->getLinkTargetByClass($link, "", '', true, false);
            $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->requested_postvar);
                                
            $no_disp_class = (strpos($this->getValue(), "|"))
                ? ""
                : " ilNoDisplay";

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

            if ($has_int) {
                $int = new ilRadioOption($lng->txt("form_link_internal"), "int");
                $int->addSubItem($ne);
            }
            
            $mode = new ilRadioGroupInputGUI("", $this->getPostVar() . "_mode");
            if (!$this->getRequired()) {
                $no = new ilRadioOption($lng->txt("form_no_link"), "no");
                $mode->addOption($no);
            }
            $mode->addOption($ext);
            if ($has_int) {
                $mode->addOption($int);
            }
        } else {
            $mode = new ilHiddenInputGUI($this->getPostVar() . "_mode");
            if ($has_int) {
                $mode->setValue("int");
            } else {
                $mode->setValue("ext");
            }
        }

        // list mode
        if ($has_list) {
            $mode_type = new ilRadioGroupInputGUI("", $this->getPostVar() . "_mode_type");
            $mode_single = new ilRadioOption($lng->txt("webr_link_type_single"), "single");
            $mode_type->addOption($mode_single);
            $mode_list = new ilRadioOption($lng->txt("webr_link_type_list"), "list");
            $mode_type->addOption($mode_list);
            $mode = new ilRadioGroupInputGUI($lng->txt("webr_link_target"), $this->getPostVar() . "_mode");
            if (!$this->getRequired()) {
                $no = new ilRadioOption($lng->txt("form_no_link"), "no");
                $mode->addOption($no);
            }
            $ext = new ilRadioOption($lng->txt("form_link_external"), "ext");
            $ext->addSubItem($ti);
            $int = new ilRadioOption($lng->txt("form_link_internal"), "int");
            $int->addSubItem($ne);
            $mode->addOption($ext);
            $mode->addOption($int);
            $mode_single->addSubItem($mode);
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
                $hidden_target->setValue($value[2] ?? "");
                
                $itpl->setVariable("VAL_OBJECT_TYPE", $value_trans["type"]);
                $itpl->setVariable("VAL_OBJECT_NAME", $value_trans["name"]);
                if (($value[2] ?? "") != "") {
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
            $ti->setValue("https://");
        }

        if ($has_int) {
            $ne->setValue($itpl->get());
        }
            
        // to html
        if ($has_radio) {
            $html = $mode->render();
        } else {
            $html = $mode->getToolbarHTML();
            
            if ($has_ext) {
                $html .= $ti->getToolbarHTML();
            } elseif ($has_int) {
                $html .= $ne->render() .
                    '<div class="help-block">' . $ne->getInfo() . '</div>';
            }
        }
        if ($has_list) {
            $html = $mode_type->render();
        }

        // js for internal link
        if ($has_int) {
            $html .= $hidden_type->getToolbarHTML() .
                $hidden_id->getToolbarHTML() .
                $hidden_target->getToolbarHTML();
        }
        
        return $html;
    }
    
    public function getContentOutsideFormTag() : string
    {
        if ($this->getAllowedLinkTypes() == self::INT ||
            $this->getAllowedLinkTypes() == self::BOTH ||
            $this->getAllowedLinkTypes() == self::LIST) {
            // as the ajax-panel uses a form it has to be outside of the parent form!
            return ilInternalLinkGUI::getInitHTML("");
        }
        return "";
    }
    
    public static function isInternalLink(string $a_value) : bool
    {
        if (strpos($a_value, "|")) {
            $parts = explode("|", $a_value);
            if (sizeof($parts) == 2 || sizeof($parts) == 3) {
                // numeric id
                if (is_numeric($parts[1])) {
                    // simple type
                    if (preg_match("/^[a-zA-Z_]+$/", $parts[0])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    public static function getTranslatedValue(string $a_value) : array
    {
        global $DIC;

        $lng = $DIC->language();
        
        $value = explode("|", $a_value);
        if ($value === false || $value === []) {
            return [];
        }
        switch ($value[0]) {
            case "media":
                $type = $lng->txt("obj_mob");
                $name = ilObject::_lookupTitle((int) $value[1]);
                break;

            case "page":
                $type = $lng->txt("obj_pg");
                $name = ilLMPageObject::_lookupTitle((int) $value[1]);
                break;

            case "chap":
                $type = $lng->txt("obj_st");
                $name = ilStructureObject::_lookupTitle((int) $value[1]);
                break;

            case "term":
                $type = $lng->txt("term");
                $name = ilGlossaryTerm::_lookGlossaryTerm((int) $value[1]);
                break;

            default:
                $type = $lng->txt("obj_" . $value[0]);
                $name = ilObject::_lookupTitle(ilObject::_lookupObjId((int) $value[1]));
                break;
        }
        return array("type" => $type, "name" => $name);
    }

    public function insert(ilTemplate $a_tpl) : void
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
    public function getIntLinkAttributes() : ?array
    {
        $val = explode("|", $this->getInput());
        $ret = null;
        $type = "";
        $target = "";
        if (self::isInternalLink($this->getInput())) {
            $target_frame = $val[2] ?? "";
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
    
    public function setValueByIntLinkAttributes(
        string $a_type,
        string $a_target,
        string $a_target_frame = ""
    ) : void {
        $t = explode("_", $a_target);
        $target_id = $t[3];
        $type = "";
        $map = self::getAttrTypeToType();
        if ($a_type == "RepositoryItem") {
            $type = ilObject::_lookupType((int) $target_id, true);
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

    public function getOnloadCode() : array
    {
        return [
            ilInternalLinkGUI::getOnloadCode("")
        ];
    }
}
