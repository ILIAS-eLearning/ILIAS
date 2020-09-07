<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List srt files from zip file for upload confirmation
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilMultiSrtConfirmationTable2GUI extends ilTable2GUI
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
     * @var ilObjUser
     */
    protected $user;

    protected $mob;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $ilUser = $DIC->user();

        $this->mob = $a_parent_obj->object;
        $lng->loadLanguageModule("meta");

        $this->setId("mob_msrt_upload");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);
        $this->setData($this->mob->getMultiSrtFiles());
        $this->setTitle($lng->txt("mob_multi_srt_files"));
        $this->setSelectAllCheckbox("file");

        $this->addColumn("", "", "1px", true);
        $this->addColumn($this->lng->txt("filename"), "filename");
        $this->addColumn($this->lng->txt("language"), "language");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.multi_srt_confirmation_row.html", "Services/MediaObjects");

        $this->addCommandButton("saveMultiSrt", $lng->txt("save"));
        $this->addCommandButton("cancelMultiSrt", $lng->txt("cancel"));
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;

        if ($a_set["lang"] != "") {
            $this->tpl->setCurrentBlock("cb");
            $language = $lng->txt("meta_l_" . $a_set["lang"]);
            $this->tpl->setVariable("LANGUAGE", $language);
            $this->tpl->setVariable("POST_FILE", ilUtil::prepareFormOutput($a_set["filename"]));
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("FILENAME", $a_set["filename"]);
    }
}
