<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/UIComponent/Explorer/classes/class.ilExplorer.php';

/**
 * Explorer for ILIAS tree
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSNodeMappingLocalExplorer extends ilExplorer
{
    const SEL_TYPE_CHECK = 1;
    const SEL_TYPE_RADIO = 2;

    private $checked_items = array();
    private $post_var = '';
    private $form_items = array();
    private $type = 0;
    
    private $sid = 0;
    private $mid = 0;
    
    private $mappings = array();

    public function __construct($a_target, $a_sid, $a_mid)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        parent::__construct($a_target);
        
        $this->sid = $a_sid;
        $this->mid = $a_mid;

        $this->type = self::SEL_TYPE_RADIO;
        
        $this->tree = $tree;
        $this->setRoot($tree->readRootId());
        $this->setOrderColumn('title');


        // reset filter
        $this->filter = array();

        $this->addFilter('root');
        $this->addFilter('cat');

        $this->addFormItemForType('root');
        $this->addFormItemForType('cat');

        $this->setFiltered(true);
        $this->setFilterMode(IL_FM_POSITIVE);
        
        $this->initMappings();
    }
    
    public function getSid()
    {
        return $this->sid;
    }
    
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * no item is clickable
     * @param <type> $a_type
     * @param <type> $a_ref_id
     * @param <type> $a_obj_id
     * @return <type>
     */
    public function isClickable($a_type, $a_ref_id = 0, $a_obj_id = 0)
    {
        return false;
    }

    /**
     * Add form item
     * @param <type> $type
     */
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

    public function getCheckedItems()
    {
        return (array) $this->checked_items;
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

    public function buildFormItem($a_node_id, $a_type)
    {
        if (!array_key_exists($a_type, $this->form_items) || !$this->form_items[$a_type]) {
            return '';
        }

        switch ($this->type) {
            case self::SEL_TYPE_CHECK:
                return ilUtil::formCheckbox((int) $this->isItemChecked($a_node_id), $this->post_var, $a_node_id);
                break;

            case self::SEL_TYPE_RADIO:
                return ilUtil::formRadioButton((int) $this->isItemChecked($a_node_id), $this->post_var, $a_node_id, "document.getElementById('map').submit(); return false;");
                break;
        }
    }

    public function formatObject($tpl, $a_node_id, $a_option, $a_obj_id = 0)
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (!isset($a_node_id) or !is_array($a_option)) {
            $this->ilias->raiseError(get_class($this) . "::formatObject(): Missing parameter or wrong datatype! " .
                                    "node_id: " . $a_node_id . " options:" . var_dump($a_option), $this->ilias->error_obj->WARNING);
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
            $tpl->setVariable("ICON_IMAGE", $this->getImage("icon_" . $a_option["type"] . ".svg", $a_option["type"], $a_obj_id));

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
            $tpl->setVariable(
                "TITLE",
                $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"])
            );
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
            $tpl->setCurrentBlock("text");
            $tpl->setVariable(
                "OBJ_TITLE",
                $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"])
            );
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
        global $DIC;

        $lng = $DIC['lng'];
        $ilias = $DIC['ilias'];
        $tree = $DIC['tree'];

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

        if ($this->isMapped(ROOT_FOLDER_ID)) {
            $tpl->setVariable(
                'OBJ_TITLE',
                '<font style="font-weight: bold">' . $title . '</font>'
            );
        } else {
            $tpl->setVariable('OBJ_TITLE', $title);
        }
    }
    
    /**
     * Format title (bold for direct mappings, italic for child mappings)
     * @param type $title
     * @param type $a_obj_id
     * @param type $a_type
     * @return type
     */
    public function buildTitle($title, $a_obj_id, $a_type)
    {
        if ($this->isMapped($a_obj_id)) {
            return '<font style="font-weight: bold">' . $title . '</font>';
        }
        if ($this->hasParentMapping($a_obj_id)) {
            return '<font style="font-style: italic">' . $title . '</font>';
        }
        return $title;
    }
    
    /**
     * Init (read) current mappings
     */
    protected function initMappings()
    {
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMappingRule.php';
        $mappings = array();
        foreach (ilECSCourseMappingRule::getRuleRefIds($this->getSid(), $this->getMid()) as $ref_id) {
            $mappings[$ref_id] = array();
        }
        
        foreach ($mappings as $ref_id => $tmp) {
            $this->mappings[$ref_id] = $GLOBALS['DIC']['tree']->getPathId($ref_id, 1);
        }
        return true;
    }
    
    protected function isMapped($a_ref_id)
    {
        return array_key_exists($a_ref_id, $this->mappings);
    }
    
    protected function hasParentMapping($a_ref_id)
    {
        foreach ($this->mappings as $ref_id => $parent_nodes) {
            if (in_array($a_ref_id, $parent_nodes)) {
                return true;
            }
        }
        return false;
    }
}
