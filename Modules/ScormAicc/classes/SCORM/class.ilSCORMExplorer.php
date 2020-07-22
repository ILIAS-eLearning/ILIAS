<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Explorer View for SCORM Learning Modules
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMTree.php");

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
    public function __construct($a_target, &$a_slm_obj)
    {
        parent::__construct($a_target);
        $this->slm_obj = $a_slm_obj;
        $this->tree = new ilSCORMTree($a_slm_obj->getId());
        $this->root_id = $this->tree->readRootId();
        $this->checkPermissions(false);
        $this->outputIcons(true);
        $this->setOrderColumn("");
    }
    
    public function getItem($a_node_id)
    {
        return new ilSCORMItem($a_node_id);
    }
    
    public function getIconImagePathPrefix()
    {
        return "scorm/";
    }
    
    public function getNodesToSkip()
    {
        return 2;
    }
    

    /**
    * overwritten method from base class
    * @access	public
    * @param	integer obj_id
    * @param	integer array options
    */
    public function formatHeader($tpl, $a_obj_id, $a_option)
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
    * @return	string
    */
    public function createTarget($a_type, $a_child, $a_highlighted_subtree = false, $a_append_anch = true)
    {
        // SET expand parameter:
        //     positive if object is expanded
        //     negative if object is compressed
        $a_child = ($a_type == '+')
            ? $a_child
            : -(int) $a_child;

        return $_SERVER["PATH_INFO"] . "?cmd=explorer&ref_id=" . $this->slm_obj->getRefId() . "&scexpand=" . $a_child;
    }

    /**
     * possible output array is set
     * @param int 	$parent_id
     */
    public function setOutput($parent_id, $a_depth = 1, $a_obj_id = 0, $a_highlighted_subtree = false)
    {
        $this->format_options = $this->createOutputArray($parent_id);
    }

    /**
     * recursivi creating of outputs
     * @param int 	$a_parent_id
     * @param array 	$options 		existing output options
     *
     * @return array $options
     */
    protected function createOutputArray($a_parent_id, $options = array())
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
    public function isVisible($a_id, $a_type)
    {
        if ($a_type == "sre") {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Creates output template
     *
     * @access	public
     *
     * @return	string
     */
    public function getOutput($jsApi = false)
    {
        $output = $this->createOutput($this->format_options, $jsApi);

        return $output->get();
    }

    /**
     * recursive creation of output templates
     *
     * @param array 		$option
     * @param bool 			$jsApi
     *
     * @return ilTemplate 	$tpl
     */
    public function createOutput($option, $jsApi)
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
     * @param string 	$a_type
     * @param int 		$a_id
     * @param int 		$a_obj
     *
     * @return bool
     */
    public function isClickable($a_type, $a_id = 0, $a_obj = 0)
    {
        if ($a_type != "sit") {
            return false;
        } else {
            if (is_object($a_obj)) {
                $sc_object = $a_obj;
            } else {
                $sc_object = new ilSCORMItem($a_id);
            }
            if ($sc_object->getIdentifierRef() != "") {
                return true;
            }
        }
        return false;
    }

    /**
     * insert the option data in $tpl
     *
     * @param array 		$option
     * @param ilTemplate 	$tpl
     * @param bool 			$jsApi
     *
     * @return ilTemplate 	$tpl
     */
    protected function insertObject($option, ilTemplate $tpl, $jsApi)
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
     * @param ilTemplate 	$tpl
     * @param array 		$a_option
     * @param int 			$a_node_id
     * @param string 		$scormtype
     */
    public function getOutputIcons(&$tpl, $a_option, $a_node_id, $scormtype = "sco")
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
