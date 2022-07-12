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
 * TableGUI class for subtitle list
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMobSubtitleTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjMediaObject $a_mob
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($a_mob->getSrtFiles());
        $this->setTitle($lng->txt("mob_subtitle_files"));
        
        $this->addColumn("", "", 1);
        $this->addColumn($this->lng->txt("mob_file"));
        $this->addColumn($this->lng->txt("mob_language"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.srt_files_row.html", "Services/MediaObjects");

        $this->addMultiCommand("confirmSrtDeletion", $lng->txt("delete"));
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $this->tpl->setVariable("FILE_NAME", $a_set["full_path"]);
        $this->tpl->setVariable("LANGUAGE", $lng->txt("meta_l_" . $a_set["language"]));
        $this->tpl->setVariable("LANG_KEY", $a_set["language"]);
    }
}
