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
 * TableGUI class for interactive image overlays
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCIIMOverlaysTableGUI extends ilTable2GUI
{
    protected ilObjMediaObject $mob;
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        $a_mob
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->mob = $a_mob;
        $this->setData($this->getOverlays());
        $this->setTitle($lng->txt("cont_overlay_images"));
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("thumbnail"), "", "20px");
        $this->addColumn($this->lng->txt("filename"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.iim_overlays_row.html", "Services/COPage");

        $this->addMultiCommand("confirmDeleteOverlays", $lng->txt("delete"));
    }
    
    public function getOverlays() : array
    {
        $ov = array();
        $files = $this->mob->getFilesOfDirectory("overlays");
        foreach ($files as $f) {
            $ov[] = array("filename" => $f);
        }
        return $ov;
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $this->tpl->setVariable("FILENAME", $a_set["filename"]);
        $piname = pathinfo($a_set["filename"]);
        $th_path = ilObjMediaObject::getThumbnailPath(
            $this->mob->getId(),
            basename($a_set["filename"], "." . $piname['extension']) . ".png"
        );
        if (!is_file($th_path)) {
            $this->mob->makeThumbnail(
                "overlays/" . $a_set["filename"],
                basename($a_set["filename"], "." . $piname['extension']) . ".png"
            );
        }
        if (is_file($th_path)) {
            $this->tpl->setVariable("THUMB", ilUtil::img($th_path));
        }
    }
}
