<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for interactive image overlays
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilPCIIMOverlaysTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_mob)
    {
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
    
    /**
     * Get overlays
     *
     * @return array array of overlays
     */
    public function getOverlays()
    {
        $ov = array();
        $files = $this->mob->getFilesOfDirectory("overlays");
        foreach ($files as $f) {
            $ov[] = array("filename" => $f);
        }
        return $ov;
    }
    
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
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
