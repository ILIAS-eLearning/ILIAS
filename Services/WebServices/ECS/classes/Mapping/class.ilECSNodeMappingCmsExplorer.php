<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/UIComponent/Explorer/classes/class.ilExplorer.php';

/**
 * Explorer for ILIAS tree
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSNodeMappingCmsExplorer extends ilExplorer
{
    const SEL_TYPE_CHECK = 1;
    const SEL_TYPE_RADIO = 2;

    private $server_id;
    private $mid;
    private $tree_id;

    private $checked_items = array();
    private $post_var = '';
    private $form_items = array();
    private $type = 0;

    public function __construct($a_target, $a_server_id, $a_mid, $a_tree_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        parent::__construct($a_target);

        $this->type = self::SEL_TYPE_CHECK;
        $this->setOrderColumn('title');
        $this->setTitleLength(1024);

        // reset filter
        $this->filter = array();
        $this->addFormItemForType('');

        $this->server_id = $a_server_id;
        $this->mid = $a_mid;
        $this->tree_id = $a_tree_id;
    }

    /**
     * Set cms tree
     */
    public function setTree(ilECSCmsTree $tree)
    {
        $this->tree = $tree;
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
        
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        $status = ilECSCmsData::lookupStatusByObjId(
            $this->server_id,
            $this->mid,
            $this->tree_id,
            $a_node_id
        );
        
        if ($status == ilECSCmsData::MAPPING_DELETED) {
            return ilUtil::formCheckbox((int) $this->isItemChecked($a_node_id), $this->post_var, $a_node_id, true);
        }
        switch ($this->type) {
            case self::SEL_TYPE_CHECK:
                return ilUtil::formCheckbox((int) $this->isItemChecked($a_node_id), $this->post_var, $a_node_id);
                break;

            case self::SEL_TYPE_RADIO:
                return ilUtil::formRadioButton((int) $this->isItemChecked($a_node_id), $this->post_var, $a_node_id);
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
            $tpl->setVariable("ICON_IMAGE", $this->getImage("icon_cat.svg", $a_option["type"], $a_obj_id));

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
            $tpl->setVariable("TITLE", ilUtil::shortenText(
                $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
                $this->textwidth,
                true
            ));
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
            $tpl->setVariable("OBJ_TITLE", ilUtil::shortenText(
                $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
                $this->textwidth,
                true
            ));
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

        // custom icons
        $path = ilObject::_getIcon($a_obj_id, "tiny", "root");


        $tpl->setCurrentBlock("icon");
        $nd = $this->tree->getNodeData($this->getRoot());

        $title = $nd["title"];

        $tpl->setVariable("ICON_IMAGE", $path);
        $tpl->setVariable("TXT_ALT_IMG", $title);
        $tpl->parseCurrentBlock();

        if (strlen($formItem = $this->buildFormItem($a_obj_id, $a_option['type']))) {
            $tpl->setCurrentBlock('check');
            $tpl->setVariable('OBJ_CHECK', $formItem);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('OBJ_TITLE', $this->buildTitle($title, $a_obj_id, ''));
    }

    public function buildTitle($title, $a_obj_id, $a_type)
    {
        if (strlen($title) >= 22) {
            #$title = substr($title, 0,22).'...';
        }
        
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        $status = ilECSCmsData::lookupStatusByObjId(
            $this->server_id,
            $this->mid,
            $this->tree_id,
            $a_obj_id
        );
        
        

        switch ($status) {
            case ilECSCmsData::MAPPING_UNMAPPED:
                return '<font style="font-weight: bold">' . $title . '</font>';

            case ilECSCmsData::MAPPING_PENDING_DISCONNECTABLE:
                return '<font style="font-weight: bold;font-style: italic">' . $title . '</font>';

            case ilECSCmsData::MAPPING_PENDING_NOT_DISCONNECTABLE:
                return '<font style="font-style: italic">' . $title . '</font>';

            case ilECSCmsData::MAPPING_MAPPED:
                return $title;

            case ilECSCmsData::MAPPING_DELETED:
                return '<font class="warning">' . $title . '</font>';

            default:
                return $title;
        }
    }
}
