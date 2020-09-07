<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Upload SRT files to a set of media objects
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesMediaObjects
 */
class ilMobMultiSrtUploadGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    public $multi_srt;

    /**
     * Constructor
     *
     * @param ilObjLearningModule $a_lm learning module object
     */
    public function __construct(ilMobMultiSrtInt $a_multi_srt)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilToolbar = $DIC->toolbar();
        $tpl = $DIC["tpl"];

        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        include_once("./Services/MediaObjects/classes/class.ilMobMultiSrtUpload.php");
        $this->multi_srt = new ilMobMultiSrtUpload($a_multi_srt);
        $this->toolbar = $ilToolbar;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("uploadMultipleSubtitleFileForm");

        if (in_array($cmd, array("uploadMultipleSubtitleFileForm", "uploadMultipleSubtitleFile", "showMultiSubtitleConfirmationTable", "cancelMultiSrt", "saveMultiSrt"))) {
            $this->$cmd();
        }
    }

    /**
     *	Upload multiple stubtitles
     */
    public function uploadMultipleSubtitleFileForm()
    {
        ilUtil::sendInfo($this->lng->txt("cont_upload_multi_srt_howto"));

        // upload file
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this), true);
        include_once("./Services/Form/classes/class.ilFileInputGUI.php");
        $fi = new ilFileInputGUI($this->lng->txt("cont_subtitle_file") . " (.zip)", "subtitle_file");
        $fi->setSuffixes(array("zip"));
        $this->toolbar->addInputItem($fi, true);

        $this->toolbar->addFormButton($this->lng->txt("upload"), "uploadMultipleSubtitleFile");
    }

    /**
     * Upload multiple subtitles
     */
    public function uploadMultipleSubtitleFile()
    {
        try {
            $this->multi_srt->uploadMultipleSubtitleFile(ilUtil::stripSlashesArray($_FILES["subtitle_file"]));
            $this->ctrl->redirect($this, "showMultiSubtitleConfirmationTable");
        } catch (ilLMException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, "uploadMultipleSubtitleFileForm");
        }
    }

    /**
     * List of srt files in zip file
     */
    public function showMultiSubtitleConfirmationTable()
    {
        include_once("./Services/MediaObjects/classes/class.ilMobMultiSrtConfirmationTable2GUI.php");
        $tab = new ilMobMultiSrtConfirmationTable2GUI($this, "showMultiSubtitleConfirmationTable");
        $this->tpl->setContent($tab->getHTML());
    }

    /**
     * Cancel Multi Feedback
     */
    public function cancelMultiSrt()
    {
        $this->multi_srt->clearMultiSrtDirectory();
        $this->ctrl->redirect($this, "uploadMultipleSubtitleFileForm");
    }

    /**
     * Save selected srt files as new srt files
     */
    public function saveMultiSrt()
    {
        $cnt = $this->multi_srt->moveMultiSrtFiles();
        $this->multi_srt->clearMultiSrtDirectory();

        ilUtil::sendSuccess($this->lng->txt("cont_moved_srt_files") . " (" . $cnt . ")", true);
        $this->ctrl->redirect($this, "uploadMultipleSubtitleFileForm");
    }
}
