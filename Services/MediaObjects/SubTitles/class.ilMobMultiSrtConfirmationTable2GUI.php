<?php

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
 * List srt files from zip file for upload confirmation
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMobMultiSrtConfirmationTable2GUI extends ilTable2GUI
{
    protected ilMobMultiSrtUpload $multi_srt;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->multi_srt = $a_parent_obj->multi_srt;
        $this->lng->loadLanguageModule("meta");

        $this->setId("mob_msrt_upload");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);
        $this->setData($this->multi_srt->getMultiSrtFiles());
        $this->setTitle($this->lng->txt("cont_multi_srt_files"));

        $this->addColumn($this->lng->txt("filename"));
        $this->addColumn($this->lng->txt("language"));
        $this->addColumn($this->lng->txt("mob"));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.mob_multi_srt_confirmation_row.html", "Services/MediaObjects");

        $this->addCommandButton("saveMultiSrt", $this->lng->txt("save"));
        $this->addCommandButton("cancelMultiSrt", $this->lng->txt("cancel"));
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        if ($a_set["lang"] != "") {
            $language = $lng->txt("meta_l_" . $a_set["lang"]);
            $this->tpl->setVariable("LANGUAGE", $language);
        }
        if ($a_set["mob"] != "") {
            $this->tpl->setVariable("MEDIA_OBJECT", $a_set["mob_title"]);
        } else {
            $this->tpl->setVariable("MEDIA_OBJECT", "-");
        }
        $this->tpl->setVariable("FILENAME", $a_set["filename"]);
    }
}
