<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Repository/classes/class.ilRepositoryExplorer.php';

/*
* ilPasteIntoMultipleItemsExplorer Explorer
*
* @author Michael Jansen <mjansen@databay.de>
*
*/
class ilPasteIntoMultipleItemsExplorer extends ilRepositoryExplorer
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    const SEL_TYPE_CHECK = 1;
    const SEL_TYPE_RADIO = 2;
    
    public $root_id = 0;
    public $output = '';
    public $ctrl = null;
    
    private $checked_items = array();
    private $post_var = '';
    private $form_items = array();
    private $form_item_permission = 'read';
    private $type = 0;
    
    /**
    * Constructor
    * @access	public
    * @param	string	$a_target scriptname
    * @param	string	$a_session_variable session_variable
    */
    public function __construct($a_type, $a_target, $a_session_variable)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();

        $this->setId("cont_paste_explorer");
        
        $this->ctrl = $ilCtrl;
        $this->type = $a_type;

        parent::__construct($a_target);
        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = 'title';
        $this->setSessionExpandVariable($a_session_variable);
        
        // reset filter
        $this->filter = array();
        
        $this->addFilter('root');
        $this->addFilter('crs');
        $this->addFilter('grp');
        $this->addFilter('cat');
        $this->addFilter('fold');
        
        $this->addFormItemForType('root');
        $this->addFormItemForType('crs');
        $this->addFormItemForType('grp');
        $this->addFormItemForType('cat');
        $this->addFormItemForType('fold');
        
        $this->setFiltered(true);
        $this->setFilterMode(IL_FM_POSITIVE);
    }
    
    public function isClickable($a_type, $a_ref_id = 0, $a_obj_id = 0)
    {
        return false;
    }
    
    public function addFormItemForType($type)
    {
        $this->form_items[$type] = true;
    }
    public function removeFormItemForType($type)
    {
        $this->form_items[$type] = false;
    }
    public function setCheckedItems($a_checked_items = array())
    {
        $this->checked_items = $a_checked_items;
    }
    public function isItemChecked($a_id)
    {
        return in_array($a_id, $this->checked_items) ? true : false;
    }
    public function setPostVar($a_post_var)
    {
        $this->post_var = $a_post_var;
    }
    public function getPostVar()
    {
        return $this->post_var;
    }
    
    /**
     * Set required perission for form item visibility
     * @param type $a_node_id
     * @param type $a_type
     * @return string
     */
    public function setRequiredFormItemPermission($a_form_item_permission)
    {
        $this->form_item_permission = $a_form_item_permission;
    }
    
    /**
     * Get required permission
     * @return string
     */
    public function getRequiredFormItemPermission()
    {
        return $this->form_item_permission;
    }
    
    public function buildFormItem($a_node_id, $a_type)
    {
        $access = $this->access;

        // permission check
        if (!$access->checkAccess($this->getRequiredFormItemPermission(), '', $a_node_id)) {
            return '';
        }
        
        if (
                !array_key_exists($a_type, $this->form_items) ||
                !$this->form_items[$a_type]
        ) {
            return '';
        }
        
        $disabled = false;
        if (is_array($_SESSION["clipboard"]["ref_ids"])) {
            $disabled = in_array($a_node_id, $_SESSION["clipboard"]["ref_ids"]);
        } elseif ((int) $_SESSION["clipboard"]["ref_ids"]) {
            $disabled = $a_node_id == $_SESSION["clipboard"]["ref_ids"];
        } elseif ($_SESSION["clipboard"]["cmd"] == 'copy' && $a_node_id == $_SESSION["clipboard"]["parent"]) {
            $disabled = true;
        }

        switch ($this->type) {
            case self::SEL_TYPE_CHECK:
                return ilUtil::formCheckbox((int) $this->isItemChecked($a_node_id), $this->post_var, $a_node_id, $disabled);
                break;
                
            case self::SEL_TYPE_RADIO:
                return ilUtil::formRadioButton((int) $this->isItemChecked($a_node_id), $this->post_var, $a_node_id, '', $disabled);
                break;
        }
    }
    
    public function formatObject($tpl, $a_node_id, $a_option, $a_obj_id = 0)
    {
        $lng = $this->lng;
        $ilErr = $this->error;
        
        if (!isset($a_node_id) or !is_array($a_option)) {
            $ilErr->raiseError(get_class($this) . "::formatObject(): Missing parameter or wrong datatype! " .
                                    "node_id: " . $a_node_id . " options:" . var_dump($a_option), $ilErr->WARNING);
        }

        $pic = false;
        foreach ($a_option["tab"] as $picture) {
            if ($picture == 'plus') {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $lng->txt("expand"));
                $target = $this->createTarget('+', $a_node_id);
                $tpl->setVariable("LINK_NAME", $a_node_id);
                $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                $tpl->setVariable("IMGPATH", $this->getImage("browser/plus.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }

            if ($picture == 'minus' && $this->show_minus) {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $lng->txt("collapse"));
                $target = $this->createTarget('-', $a_node_id);
                $tpl->setVariable("LINK_NAME", $a_node_id);
                $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                $tpl->setVariable("IMGPATH", $this->getImage("browser/minus.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }
        }
        
        if (!$pic) {
            $tpl->setCurrentBlock("blank");
            $tpl->setVariable("BLANK_PATH", $this->getImage("browser/blank.png"));
            $tpl->parseCurrentBlock();
        }

        if ($this->output_icons) {
            $tpl->setCurrentBlock("icon");
            
            $path = ilObject::_getIcon($a_obj_id, "tiny", $a_option["type"]);
            $tpl->setVariable("ICON_IMAGE", $path);
            
            $tpl->setVariable("TARGET_ID", "iconid_" . $a_node_id);
            $this->iconList[] = "iconid_" . $a_node_id;
            $tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
            $tpl->parseCurrentBlock();
        }
        
        if (strlen($formItem = $this->buildFormItem($a_node_id, $a_option['type']))) {
            $tpl->setCurrentBlock('check');
            $tpl->setVariable('OBJ_CHECK', $formItem);
            $tpl->parseCurrentBlock();
        }

        if ($this->isClickable($a_option["type"], $a_node_id, $a_obj_id)) {	// output link
            $tpl->setCurrentBlock("link");
            //$target = (strpos($this->target, "?") === false) ?
            //	$this->target."?" : $this->target."&";
            //$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
            $tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["type"]));
                
            $style_class = $this->getNodeStyleClass($a_node_id, $a_option["type"]);
            
            if ($style_class != "") {
                $tpl->setVariable("A_CLASS", ' class="' . $style_class . '" ');
            }

            if (($onclick = $this->buildOnClick($a_node_id, $a_option["type"], $a_option["title"])) != "") {
                $tpl->setVariable("ONCLICK", "onClick=\"$onclick\"");
            }

            $tpl->setVariable("LINK_NAME", $a_node_id);
            $tpl->setVariable("TITLE", $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]));
            $tpl->setVariable("DESC", ilUtil::shortenText(
                $this->buildDescription($a_option["description"], $a_node_id, $a_option["type"]),
                $this->textwidth,
                true
            ));
            $frame_target = $this->buildFrameTarget($a_option["type"], $a_node_id, $a_option["obj_id"]);
            if ($frame_target != "") {
                $tpl->setVariable("TARGET", " target=\"" . $frame_target . "\"");
            }
            $tpl->parseCurrentBlock();
        } else {			// output text only
            $obj_title = $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]);
            
            // highlight current node
            if ($a_node_id == $this->highlighted) {
                $obj_title = "<span class=\"ilHighlighted\">" . $obj_title . "</span>";
            }
            
            $tpl->setCurrentBlock("text");
            $tpl->setVariable("OBJ_TITLE", $obj_title);
            $tpl->setVariable("OBJ_DESC", ilUtil::shortenText(
                $this->buildDescription($a_option["desc"], $a_node_id, $a_option["type"]),
                $this->textwidth,
                true
            ));
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("list_item");
        $tpl->parseCurrentBlock();
        $tpl->touchBlock("element");
    }
    
    /*
    * overwritten method from base class
    * @access	public
    * @param	integer obj_id
    * @param	integer array options
    * @return	string
    */
    public function formatHeader($tpl, $a_obj_id, $a_option)
    {
        $lng = $this->lng;
        $tree = $this->tree;

        // custom icons
        $path = ilObject::_getIcon($a_obj_id, "tiny", "root");
        

        $tpl->setCurrentBlock("icon");
        $nd = $tree->getNodeData(ROOT_FOLDER_ID);
        $title = $nd["title"];
        if ($title == "ILIAS") {
            $title = $lng->txt("repository");
        }

        $tpl->setVariable("ICON_IMAGE", $path);
        $tpl->setVariable("TXT_ALT_IMG", $title);
        $tpl->parseCurrentBlock();

        if (strlen($formItem = $this->buildFormItem($a_obj_id, $a_option['type']))) {
            $tpl->setCurrentBlock('check');
            $tpl->setVariable('OBJ_CHECK', $formItem);
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable('OBJ_TITLE', $title);
    }
    
    public function showChilds($a_ref_id, $a_obj_id = 0)
    {
        $ilAccess = $this->access;

        if ($a_ref_id == 0) {
            return true;
        }
        // #11778 - ilAccessHandler::doConditionCheck()
        if ($ilAccess->checkAccess("read", "", $a_ref_id)) {
            return true;
        } else {
            return false;
        }
    }

    public function isVisible($a_ref_id, $a_type)
    {
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess('visible', '', $a_ref_id)) {
            return false;
        }

        return true;
    }

}
