<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Explorer for ILIAS tree
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSNodeMappingCmsExplorer extends ilExplorer
{
    public const SEL_TYPE_CHECK = 1;
    public const SEL_TYPE_RADIO = 2;

    private int $server_id;
    private int $mid;
    private int $tree_id;

    private array $checked_items = array();
    private string $post_var = '';
    private array $form_items = array();
    private int $type;

    public function __construct(string $a_target, int $a_server_id, int $a_mid, int $a_tree_id)
    {
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
    public function setTree(ilECSCmsTree $tree) : void
    {
        $this->tree = $tree;
    }

    /**
     * no item is clickable
     */
    public function isClickable(string $type, int $ref_id = 0) : bool
    {
        return false;
    }

    /**
     * Add form item
     */
    public function addFormItemForType($type) : void
    {
        $this->form_items[$type] = true;
    }

    public function removeFormItemForType($type) : void
    {
        $this->form_items[$type] = false;
    }

    public function setCheckedItems($a_checked_items = array()) : void
    {
        $this->checked_items = $a_checked_items;
    }

    public function isItemChecked($a_id) : bool
    {
        return in_array($a_id, $this->checked_items, true) ? true : false;
    }

    public function setPostVar($a_post_var) : void
    {
        $this->post_var = $a_post_var;
    }
    public function getPostVar() : string
    {
        return $this->post_var;
    }

    public function buildFormItem($a_node_id, $a_type) : string
    {
        if (!array_key_exists($a_type, $this->form_items) || !$this->form_items[$a_type]) {
            return '';
        }
        
        $status = ilECSCmsData::lookupStatusByObjId(
            $this->server_id,
            $this->mid,
            $this->tree_id,
            $a_node_id
        );
        
        if ($status === ilECSCmsData::MAPPING_DELETED) {
            return ilLegacyFormElementsUtil::formCheckbox(
                $this->isItemChecked($a_node_id),
                $this->post_var,
                $a_node_id,
                true
            );
        }
        switch ($this->type) {
            case self::SEL_TYPE_CHECK:
                return ilLegacyFormElementsUtil::formCheckbox(
                    $this->isItemChecked($a_node_id),
                    $this->post_var,
                    $a_node_id
                );
                break;

            case self::SEL_TYPE_RADIO:
                return ilLegacyFormElementsUtil::formRadioButton(
                    $this->isItemChecked($a_node_id),
                    $this->post_var,
                    $a_node_id
                );
                break;
        }

        return '';
    }

    public function formatObject($tpl, $a_node_id, $a_option, $a_obj_id = 0) : void
    {
        global $DIC;
        if (!isset($a_node_id) || !is_array($a_option)) {
            $this->error->raiseError(get_class($this) . "::formatObject(): Missing parameter or wrong datatype! " .
                                    "node_id: " . $a_node_id . " options:" . print_r($a_option, true), $this->error->WARNING);
        }

        $pic = false;
        foreach ($a_option["tab"] as $picture) {
            if ($picture === 'plus') {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $this->lng->txt("expand"));
                $target = $this->createTarget('+', $a_node_id);
                $tpl->setVariable("LINK_NAME", $a_node_id);
                $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                $tpl->setVariable("IMGPATH", $this->getImage("browser/plus.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }

            if ($picture === 'minus' && $this->show_minus) {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $this->lng->txt("collapse"));
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
            $tpl->setVariable("TXT_ALT_IMG", $this->lng->txt($a_option["desc"]));
            $tpl->parseCurrentBlock();
        }

        if ($formItem = ($this->buildFormItem($a_node_id, $a_option['type']) !== '')) {
            $tpl->setCurrentBlock('check');
            $tpl->setVariable('OBJ_CHECK', $formItem);
            $tpl->parseCurrentBlock();
        }

        if ($this->isClickable($a_option["type"], $a_node_id)) {	// output link
            $tpl->setCurrentBlock("link");
            //$target = (strpos($this->target, "?") === false) ?
            //	$this->target."?" : $this->target."&";
            //$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
            $tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["type"]));

            $style_class = $this->getNodeStyleClass($a_node_id, $a_option["type"]);

            if ($style_class !== "") {
                $tpl->setVariable("A_CLASS", ' class="' . $style_class . '" ');
            }

            if (($onclick = $this->buildOnClick($a_node_id, $a_option["type"], $a_option["title"])) !== "") {
                $tpl->setVariable("ONCLICK", "onClick=\"$onclick\"");
            }

            $tpl->setVariable("LINK_NAME", $a_node_id);
            $tpl->setVariable(
                "TITLE",
                ilStr::shortenTextExtended(
                    $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
                    $this->textwidth,
                    true
                )
            );
            $tpl->setVariable(
                "DESC",
                ilStr::shortenTextExtended(
                    $this->buildDescription($a_option["description"], $a_node_id, $a_option["type"]),
                    $this->textwidth,
                    true
                )
            );
            $frame_target = $this->buildFrameTarget($a_option["type"], $a_node_id, $a_option["obj_id"]);
            if ($frame_target !== "") {
                $tpl->setVariable("TARGET", " target=\"" . $frame_target . "\"");
            }
        } else {			// output text only
            $tpl->setCurrentBlock("text");
            $tpl->setVariable(
                "OBJ_TITLE",
                ilStr::shortenTextExtended(
                    $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
                    $this->textwidth,
                    true
                )
            );
            $tpl->setVariable(
                "OBJ_DESC",
                ilStr::shortenTextExtended(
                    $this->buildDescription($a_option["desc"], $a_node_id, $a_option["type"]),
                    $this->textwidth,
                    true
                )
            );
        }
        $tpl->parseCurrentBlock();

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
    public function formatHeader(ilTemplate $tpl, $a_obj_id, array $a_option) : void
    {
        // custom icons
        $path = ilObject::_getIcon((int) $a_obj_id, "tiny", "root");


        $tpl->setCurrentBlock("icon");
        $nd = $this->tree->getNodeData($this->getRoot());

        $title = $nd["title"];

        $tpl->setVariable("ICON_IMAGE", $path);
        $tpl->setVariable("TXT_ALT_IMG", $title);
        $tpl->parseCurrentBlock();

        if ($formItem = ($this->buildFormItem($a_obj_id, $a_option['type']) !== '')) {
            $tpl->setCurrentBlock('check');
            $tpl->setVariable('OBJ_CHECK', $formItem);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('OBJ_TITLE', $this->buildTitle($title, $a_obj_id, ''));
    }

    public function buildTitle(string $a_title, $a_id, string $a_type) : string
    {
        $status = ilECSCmsData::lookupStatusByObjId(
            $this->server_id,
            $this->mid,
            $this->tree_id,
            $a_id
        );
        
        

        switch ($status) {
            case ilECSCmsData::MAPPING_UNMAPPED:
                return '<font style="font-weight: bold">' . $a_title . '</font>';

            case ilECSCmsData::MAPPING_PENDING_DISCONNECTABLE:
                return '<font style="font-weight: bold;font-style: italic">' . $a_title . '</font>';

            case ilECSCmsData::MAPPING_PENDING_NOT_DISCONNECTABLE:
                return '<font style="font-style: italic">' . $a_title . '</font>';

            case ilECSCmsData::MAPPING_DELETED:
                return '<font class="warning">' . $a_title . '</font>';

            case ilECSCmsData::MAPPING_MAPPED:
            default:
                return $a_title;
        }
    }
}
