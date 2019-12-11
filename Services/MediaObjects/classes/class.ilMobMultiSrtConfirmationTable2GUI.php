<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List srt files from zip file for upload confirmation
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesMediaObjects
 */
class ilMobMultiSrtConfirmationTable2GUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $mob;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
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

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
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
