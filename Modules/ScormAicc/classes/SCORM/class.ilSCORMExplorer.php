<?php
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
class ilSCORMExplorer extends ilExplorer
{

    /**
     * id of root folder
     * @var int root folder id
     * @access private
     */
    public $slm_obj;

    /**
    * Constructor
    * @access	public
    * @param	string	scriptname
    * @param    int user_id
    */
    public function __construct(string $a_target, &$a_slm_obj)
    {
        parent::__construct($a_target);
        $this->slm_obj = $a_slm_obj;
        $this->tree = new ilSCORMTree($a_slm_obj->getId());
        $this->root_id = $this->tree->readRootId();
        $this->checkPermissions(false);
        $this->outputIcons(true);
        $this->setOrderColumn("");
    }
    
    public function getItem($a_node_id): \ilSCORMItem
    {
        return new ilSCORMItem($a_node_id);
    }
    
    public function getIconImagePathPrefix(): string
    {
        return "scorm/";
    }
    
    public function getNodesToSkip(): int
    {
        return 2;
    }
    

    /**
    * overwritten method from base class
    * @access	public
    * @param	integer obj_id
    * @param	integer array options
    */
    public function formatHeader(ilTemplate $tpl, $a_obj_id, array $a_option) : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilias = $DIC['ilias'];

        $tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");

        $tpl->setCurrentBlock("row");
        $tpl->setVariable("TITLE", $lng->txt("cont_manifest"));
        $tpl->setVariable("LINK_TARGET", $this->target . "&" . $this->target_get . "=" . $a_obj_id);
        $tpl->setVariable("TARGET", " target=\"" . $this->frame_target . "\"");
        $tpl->parseCurrentBlock();

        $this->output[] = $tpl->get();
    }

    /**
    * Creates Get Parameter
    * @access	private
    * @param	string
    * @param	integer
    * @return    string
    */
    public function createTarget(string $a_type, $a_node_id, bool $a_highlighted_subtree = false, bool $a_append_anch = true) : string
    {
        // SET expand parameter:
        //     positive if object is expanded
        //     negative if object is compressed
        $a_node_id = ($a_type == '+')
            ? $a_node_id
            : -(int) $a_node_id;

        return $_SERVER["PATH_INFO"] . "?cmd=explorer&ref_id=" . $this->slm_obj->getRefId() . "&scexpand=" . $a_node_id;
    }

    /**
     * possible output array is set
     * @param int $a_parent_id
     */
    public function setOutput($a_parent_id, int $a_depth = 1, int $a_obj_id = 0, bool $a_highlighted_subtree = false) : void
    {
        $this->format_options = $this->createOutputArray($a_parent_id);
    }

    /**
				 * recursivi creating of outputs
				 * @param array 	$options 		existing output options
				 * @return array $options
				 */
				protected function createOutputArray(int $a_parent_id, array $options = array()): array
    {
        $types_do_not_display = array("sos", "sma");
        $types_do_not_load = array("srs");

        if (!isset($a_parent_id)) {
            $this->ilias->raiseError(get_class($this) . "::setOutput(): No node_id given!", $this->ilias->error_obj->WARNING);
        }

        if (!$this->showChilds($a_parent_id)) {
            return array();
        }

        foreach ($this->tree->getChilds($a_parent_id, $this->order_column) as $key => $child) {
            if (in_array($child["c_type"], $types_do_not_load)) {
                continue;
            }

            $option = array();
            $option["parent"] = $child["parent"];
            $option["id"] = $child["child"];
            $option["title"] = $child["title"];
            $option["c_type"] = $child["c_type"];
            $option["obj_id"] = $child["obj_id"];
            $option["desc"] = "obj_" . $child["c_type"];
            $option["container"] = false;
            $option["visible"] = !in_array($child["c_type"], $types_do_not_display);

            if ($this->showChilds($option["id"])) {
                $option = $this->createOutputArray($option["id"], $option);
            }

            $options["childs"][] = $option;
        }

        return $options;
    }

    /**
     * @inheritdoc
     */
    public function isVisible($a_ref_id, string $a_type) : bool
    {
        if ($a_type == "sre") {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Creates output template
     * @access	public
     * @return	string
     */
    public function getOutput($jsApi = false) : string
    {
        $output = $this->createOutput($this->format_options, $jsApi);

        return $output->get();
    }

    /**
				 * recursive creation of output templates
				 *
				 *
				 * @return ilTemplate 	$tpl
				 * @param mixed[] $option
				 */
				public function createOutput(array $option, bool $jsApi): \ilTemplate
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];

        if ($option["visible"]) {
            $tpl = new ilTemplate("tpl.sahs_tree_ul.html", true, true, "Modules/ScormAicc");
            $tpl = $this->insertObject($option, $tpl, $jsApi);
        } else {
            $tpl = new ilTemplate("tpl.sahs_tree_free.html", true, true, "Modules/ScormAicc");
        }

        if (is_array($option["childs"]) && count($option["childs"])) {
            foreach ($option["childs"] as $key => $ch_option) {
                $tpl->setCurrentBlock("childs");
                $tpl->setVariable("CHILDS", $this->createOutput($ch_option, $jsApi)->get());
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl;
    }

    /**
     * can i click on the module name
     * @param string $a_type
     * @param int    $a_ref_id
     * @return bool
     */
    public function isClickable(string $a_type, $a_ref_id = 0) : bool
    {
        if ($a_type != "sit") {
            return false;
        } else {
            $sc_object = new ilSCORMItem($a_ref_id);
            if ($sc_object->getIdentifierRef() != "") {
                return true;
            }
        }
        return false;
    }

    /**
				 * insert the option data in $tpl
				 *
				 *
				 * @return ilTemplate 	$tpl
				 * @param mixed[] $option
				 */
				protected function insertObject(array $option, ilTemplate $tpl, bool $jsApi): \ilTemplate
    {
        if (!is_array($option) || !isset($option["id"])) {
            $this->ilias->raiseError(get_class($this) . "::insertObject(): Missing parameter or wrong datatype! " .
                                    "options:" . var_dump($option), $this->ilias->error_obj->WARNING);
        }

        //get scorm item
        $sc_object = new ilSCORMItem($option["id"]);
        $id_ref = $sc_object->getIdentifierRef();

        //get scorm resource ref id
        $sc_res_id = ilSCORMResource::_lookupIdByIdRef($id_ref, $sc_object->getSLMId());

        //get scorm type
        $scormtype = strtolower(ilSCORMResource::_lookupScormType($sc_res_id));

        //is scorm clickabke
        $clickable = $this->isClickable($option["c_type"], $option["id"], $sc_object);

        if ($this->output_icons && $clickable) {
            $this->getOutputIcons($tpl, $option, $option["id"], $scormtype);
        }

        if ($clickable) {	// output link
            $tpl->setCurrentBlock("link");
            $frame_target = $this->buildFrameTarget($option["c_type"], $option["id"], $option["obj_id"]);
            if ($frame_target != "") {
                $tpl->setVariable("TITLE", ilUtil::shortenText($option["title"], $this->textwidth, true));
                $tpl->setVariable("LINK_TARGET", "javascript:void(0);");
                if ($jsApi == true) {
                    $tpl->setVariable("ONCLICK", " onclick=\"parent.API.IliasLaunch('" . $option["id"] . "');return false;\"");
                } else {
                    $tpl->setVariable("ONCLICK", " onclick=\"parent.APIFRAME.setupApi();parent.APIFRAME.API."
                        . ($scormtype == 'asset' ? 'IliasLaunchAsset' : 'IliasLaunchSahs')
                        . "('" . $option["id"] . "');return false;\"");
                }
            }
            $tpl->parseCurrentBlock();
        } else {			// output text only
            $tpl->setCurrentBlock("text");
            $tpl->setVariable("OBJ_TITLE", ilUtil::shortenText($option["title"], $this->textwidth, true));
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("li");
        $tpl->parseCurrentBlock();

        return $tpl;
    }

    /**
				 * tpl is filled with option state
				 *
				 * @param mixed[] $a_option
				 */
				public function getOutputIcons(\ilTemplate &$tpl, array $a_option, int $a_node_id, string $scormtype = "sco"): void
    {
        global $DIC;
        $lng = $DIC['lng'];

        $tpl->setCurrentBlock("icon");

        if ($scormtype == 'asset') {
            $tpl->setVariable('ICON_IMAGE', ilUtil::getImagePath($this->getIconImagePathPrefix() . "asset.svg"));
            $tpl->setVariable('TXT_ALT_IMG', '');
            $tpl->parseCurrentBlock();
            return;
        }

        $trdata = ilSCORMItem::_lookupTrackingDataOfUser(
            $a_node_id,
            0,
            $this->slm_obj->getId()
        );

        // status
        $status = ($trdata["cmi.core.lesson_status"] == "")
                ? "not attempted"
                : $trdata["cmi.core.lesson_status"];

        $statusChar = strtolower(substr($status, 0, 1));
        if ($statusChar == "f") {
            $status = "failed";
        } elseif ($statusChar == "b") {
            $status = "browsed";
        } elseif ($statusChar == "c") {
            $status = "completed";
        } elseif ($statusChar == "n") {
            $status = "not_attempted";
        } elseif ($statusChar == "p") {
            $status = "passed";
        } elseif ($statusChar == "r") {
            $status = "running";
        }
            
        $alt = $lng->txt("cont_status") . ": " .
                $lng->txt("cont_sc_stat_" . str_replace(" ", "_", $status));

        // score
        if ($trdata["cmi.core.score.raw"] != "") {
            $alt .= ", " . $lng->txt("cont_credits") .
                ": " . $trdata["cmi.core.score.raw"];
        }

        // total time
        if ($trdata["cmi.core.total_time"] != "" &&
                $trdata["cmi.core.total_time"] != "0000:00:00.00") {
            $alt .= ", " . $lng->txt("cont_total_time") .
                ": " . $trdata["cmi.core.total_time"];
        }

        $tpl->setVariable("ICON_NAME", 'scoIcon' . $a_node_id);
        $tpl->setVariable("ICON_IMAGE", ilUtil::getImagePath($this->getIconImagePathPrefix() . str_replace(" ", "_", $status) . ".svg"));
        $tpl->setVariable("TXT_ALT_IMG", $alt);
        $tpl->parseCurrentBlock();
    }
}
