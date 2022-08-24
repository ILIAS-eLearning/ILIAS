<?php

declare(strict_types=1);
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

class ilSCORMExplorer extends ilExplorer
{
    /**
     * id of root folder
     */
    public ilObjSCORMLearningModule $slm_obj;

    public function __construct(string $a_target, ilObjSCORMLearningModule &$a_slm_obj)
    {
        parent::__construct($a_target);
        $this->slm_obj = $a_slm_obj;
        $this->tree = new ilSCORMTree($a_slm_obj->getId());
        $this->root_id = $this->tree->readRootId();
        $this->checkPermissions(false);
        $this->outputIcons(true);
        $this->setOrderColumn("");
    }

    public function getItem(int $a_node_id): \ilSCORMItem
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
     * @throws ilTemplateException
     */
    public function formatHeader(ilTemplate $tpl, $a_obj_id, array $a_option): void //Missing typehint because ilExplorer
    {
        global $DIC;
        $lng = $DIC->language();

        $tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");

        $tpl->setCurrentBlock("row");
        $tpl->setVariable("TITLE", $lng->txt("cont_manifest"));
        $tpl->setVariable("LINK_TARGET", $this->target . "&" . $this->target_get . "=" . $a_obj_id);
        $tpl->setVariable("TARGET", " target=\"" . $this->frame_target . "\"");
        $tpl->parseCurrentBlock();

        $this->output .= $tpl->get();
    }

    /**
     * Creates Get Parameter
     */
    public function createTarget(string $a_type, $a_node_id, bool $a_highlighted_subtree = false, bool $a_append_anch = true): string //Missing typehint because ilExplorer
    {
        // SET expand parameter:
        //     positive if object is expanded
        //     negative if object is compressed
        $a_node_id = ($a_type == '+')
            ? $a_node_id
            : -(int) $a_node_id;

        return $_SERVER["PATH_INFO"] . "?cmd=explorer&ref_id=" . $this->slm_obj->getRefId() . "&scexpand=" . $a_node_id; //ToDo $_SERVER?
    }

    public function setOutput($a_parent_id, int $a_depth = 1, int $a_obj_id = 0, $a_highlighted_subtree = false): void
//    public function setOutput(int $a_parent_id, int $a_depth = 1, int $a_obj_id = 0, bool $a_highlighted_subtree = false) : void
    {
        $this->format_options = $this->createOutputArray($a_parent_id);
    }

    /**
                 * recursive creating of outputs
                 * @return mixed[]
                 */
    protected function createOutputArray(int $a_parent_id, array $options = array()): array
    {
        global $ilErr;
        $types_do_not_display = array("sos", "sma");
        $types_do_not_load = array("srs");

        if (!isset($a_parent_id)) {
            $ilErr->raiseError(get_class($this) . "::setOutput(): No node_id given!", $ilErr->error_obj->WARNING);
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
                $option = $this->createOutputArray((int) $option["id"], $option);
            }

            $options["childs"][] = $option;
        }

        return $options;
    }

    public function isVisible($a_ref_id, string $a_type): bool //Typehint not possible now - see ilExplorer
    {
        return $a_type !== "sre";
    }

    /**
     * Creates output template
     * @throws ilTemplateException
     */
    public function getOutput(bool $jsApi = false): string
    {
        return $this->createOutput($this->format_options, $jsApi)->get();
    }

    /**
     * recursive creation of output templates
     * @throws ilTemplateException
     */
    public function createOutput(array $option, bool $jsApi): \ilTemplate
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];

        if (isset($option["visible"]) && $option["visible"] == true) {
            $tpl = new ilTemplate("tpl.sahs_tree_ul.html", true, true, "Modules/ScormAicc");
            $tpl = $this->insertObject($option, $tpl, $jsApi);
        } else {
            $tpl = new ilTemplate("tpl.sahs_tree_free.html", true, true, "Modules/ScormAicc");
        }

        if (isset($option["childs"]) && is_array($option["childs"]) && count($option["childs"]) > 0) {
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
     */
    public function isClickable(string $type, int $ref_id = 0): bool
    {
        if ($type !== "sit") {
            return false;
        }

        $sc_object = new ilSCORMItem($ref_id);
        return $sc_object->getIdentifierRef() != "";
    }

    /**
     * insert the option data in $tpl
     * @throws ilTemplateException
     */
    protected function insertObject(array $option, ilTemplate $tpl, bool $jsApi): \ilTemplate
    {
        global $ilErr;
        if (!is_array($option) || !isset($option["id"])) {
            $ilErr->raiseError(get_class($this) . "::insertObject(): Missing parameter or wrong datatype! " .
                                    "options:" . var_dump($option), $ilErr->error_obj->WARNING);
        }
        $clickable = false;
        if ($option["c_type"] == "sit") {
            //get scorm item
            $sc_object = new ilSCORMItem((int) $option["id"]);
            $id_ref = $sc_object->getIdentifierRef();

            //get scorm resource ref id
            $sc_res_id = ilSCORMResource::_lookupIdByIdRef($id_ref, $sc_object->getSLMId());

            //get scorm type
            $scormtype = strtolower(ilSCORMResource::_lookupScormType($sc_res_id));

            //is scorm clickabke
            $clickable = $this->isClickable($option["c_type"], (int) $option["id"]);

            if ($this->output_icons && $clickable) {
                $this->getOutputIcons($tpl, $option, (int) $option["id"], $scormtype);
            }
        }
        if ($clickable) {	// output link
            $tpl->setCurrentBlock("link");
            $frame_target = $this->buildFrameTarget($option["c_type"], $option["id"], $option["obj_id"]);
            if ($frame_target != "") {
                $tpl->setVariable("TITLE", ilStr::shortenTextExtended($option["title"], $this->textwidth, true));
                $tpl->setVariable("LINK_TARGET", "javascript:void(0);");
                if ($jsApi == true) {
                    $tpl->setVariable("ONCLICK", " onclick=\"parent.API.IliasLaunch('" . $option["id"] . "');return false;\"");
                } else {
                    $tpl->setVariable("ONCLICK", " onclick=\"parent.APIFRAME.setupApi();parent.APIFRAME.API."
                        . ($scormtype === 'asset' ? 'IliasLaunchAsset' : 'IliasLaunchSahs')
                        . "('" . $option["id"] . "');return false;\"");
                }
            }
            $tpl->parseCurrentBlock();
        } else {			// output text only
            $tpl->setCurrentBlock("text");
            $tpl->setVariable("OBJ_TITLE", ilStr::shortenTextExtended($option["title"], $this->textwidth, true));
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("li");
        $tpl->parseCurrentBlock();

        return $tpl;
    }

    /**
     * tpl is filled with option state
     * @throws ilTemplateException
     */
    public function getOutputIcons(\ilTemplate $tpl, array $a_option, int $a_node_id, string $scormtype = "sco"): void
    {
        global $DIC;
        $lng = $DIC->language();

        $tpl->setCurrentBlock("icon");

        if ($scormtype === 'asset') {
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
        $status = !isset($trdata["cmi.core.lesson_status"])
                ? "not attempted"
                : $trdata["cmi.core.lesson_status"];

        $statusChar = strtolower($status[0]);
        if ($statusChar === "f") {
            $status = "failed";
        } elseif ($statusChar === "b") {
            $status = "browsed";
        } elseif ($statusChar === "c") {
            $status = "completed";
        } elseif ($statusChar === "n") {
            $status = "not_attempted";
        } elseif ($statusChar === "p") {
            $status = "passed";
        } elseif ($statusChar === "r") {
            $status = "running";
        }

        $alt = $lng->txt("cont_status") . ": " .
                $lng->txt("cont_sc_stat_" . str_replace(" ", "_", $status));

        // score
        if (isset($trdata["cmi.core.score.raw"])) {
            $alt .= ", " . $lng->txt("cont_credits") .
                ": " . $trdata["cmi.core.score.raw"];
        }

        // total time
        if (isset($trdata["cmi.core.total_time"]) &&
                $trdata["cmi.core.total_time"] !== "0000:00:00.00") {
            $alt .= ", " . $lng->txt("cont_total_time") .
                ": " . $trdata["cmi.core.total_time"];
        }

        $tpl->setVariable("ICON_NAME", 'scoIcon' . $a_node_id);
        $tpl->setVariable("ICON_IMAGE", ilUtil::getImagePath($this->getIconImagePathPrefix() . str_replace(" ", "_", $status) . ".svg"));
        $tpl->setVariable("TXT_ALT_IMG", $alt);
        $tpl->parseCurrentBlock();
    }
}
