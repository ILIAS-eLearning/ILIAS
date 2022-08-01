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
* GUI class for SCORM Items
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMItemGUI extends ilSCORMObjectGUI
{
    public function __construct(int $a_id)
    {
        parent::__construct();
        $this->sc_object = new ilSCORMItem($a_id);
    }

    public function view() : void
    {
        global $DIC;
        $usr = $DIC->user();

        // get ressource identifier
        $id_ref = $this->sc_object->getIdentifierRef();
        if ($id_ref != "") {
            $resource = new ilSCORMResource();
            $resource->readByIdRef($id_ref, $this->sc_object->getSLMId());

            $refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
            $objId = $DIC->http()->wrapper()->query()->retrieve('obj_id', $DIC->refinery()->kindlyTo()->int());

            $slm_obj = new ilObjSCORMLearningModule($refId);

            if ($resource->getHref() != "") {
                $param_str = ($this->sc_object->getParameters() != "")
                    ? "?" . $this->sc_object->getParameters()
                    : "";

                $this->tpl = new ilGlobalTemplate("tpl.scorm_content_frameset.html", true, true, "Modules/ScormAicc");
                $this->tpl->setVariable("ITEM_LOCATION", $slm_obj->getDataDirectory() . "/" . $resource->getHref() . $param_str);
                $this->tpl->setVariable("ITEM_ID", $objId);
                $this->tpl->setVariable("REF_ID", $refId);
                $this->tpl->setVariable("USER_ID", $usr->getId());
                $this->tpl->setVariable("ADAPTER_NAME", $slm_obj->getAPIAdapterName());
                $this->tpl->printToStdout();
                exit;
            }
        }

        // this point is only reached if now resource could be displayed above!
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.scorm_obj.html", "Modules/ScormAicc");
        $this->tpl->setCurrentBlock("par_table");
        $this->tpl->setVariable("TXT_OBJECT_TYPE", $this->lng->txt("cont_item"));
        $this->displayParameter(
            $this->lng->txt("cont_import_id"),
            $this->sc_object->getImportId()
        );
        $this->displayParameter(
            $this->lng->txt("cont_id_ref"),
            $this->sc_object->getIdentifierRef()
        );
        $str_visible = ($this->sc_object->getVisible())
            ? "true"
            : "false";
        $this->displayParameter(
            $this->lng->txt("cont_is_visible"),
            $str_visible
        );
        $this->displayParameter(
            $this->lng->txt("cont_parameters"),
            $this->sc_object->getParameters()
        );
        $this->displayParameter(
            $this->lng->txt("cont_sc_title"),
            $this->sc_object->getTitle()
        );
        $this->displayParameter(
            $this->lng->txt("cont_prereq_type"),
            $this->sc_object->getPrereqType()
        );
        $this->displayParameter(
            $this->lng->txt("cont_prerequisites"),
            $this->sc_object->getPrerequisites()
        );
        $this->displayParameter(
            $this->lng->txt("cont_max_time_allowed"),
            $this->sc_object->getMaxTimeAllowed()
        );
        $this->displayParameter(
            $this->lng->txt("cont_time_limit_action"),
            $this->sc_object->getTimeLimitAction()
        );
        $this->displayParameter(
            $this->lng->txt("cont_data_from_lms"),
            $this->sc_object->getDataFromLms()
        );
        $this->displayParameter(
            $this->lng->txt("cont_mastery_score"),
            $this->sc_object->getMasteryScore()
        );
        $this->tpl->parseCurrentBlock();
    }
}
