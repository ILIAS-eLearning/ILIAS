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
 * Upload SRT files to a set of media objects
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMobMultiSrtUploadGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected ilGlobalTemplateInterface $tpl;
    public ilMobMultiSrtUpload $multi_srt;

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
        $this->multi_srt = new ilMobMultiSrtUpload($a_multi_srt);
        $this->toolbar = $ilToolbar;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd("uploadMultipleSubtitleFileForm");

        if (in_array($cmd, array("uploadMultipleSubtitleFileForm", "uploadMultipleSubtitleFile", "showMultiSubtitleConfirmationTable", "cancelMultiSrt", "saveMultiSrt"))) {
            $this->$cmd();
        }
    }

    public function uploadMultipleSubtitleFileForm(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_upload_multi_srt_howto"));

        // upload file
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this), true);
        $fi = new ilFileInputGUI($this->lng->txt("cont_subtitle_file") . " (.zip)", "subtitle_file");
        $fi->setSuffixes(array("zip"));
        $this->toolbar->addInputItem($fi, true);

        $this->toolbar->addFormButton($this->lng->txt("upload"), "uploadMultipleSubtitleFile");
    }

    /**
     * Upload multiple subtitles
     */
    public function uploadMultipleSubtitleFile(): void
    {
        try {
            $this->multi_srt->uploadMultipleSubtitleFile(ilArrayUtil::stripSlashesArray($_FILES["subtitle_file"]));
            $this->ctrl->redirect($this, "showMultiSubtitleConfirmationTable");
        } catch (ilLMException $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            $this->ctrl->redirect($this, "uploadMultipleSubtitleFileForm");
        }
    }

    /**
     * List of srt files in zip file
     */
    public function showMultiSubtitleConfirmationTable(): void
    {
        $tab = new ilMobMultiSrtConfirmationTable2GUI($this, "showMultiSubtitleConfirmationTable");
        $this->tpl->setContent($tab->getHTML());
    }

    public function cancelMultiSrt(): void
    {
        $this->multi_srt->clearMultiSrtDirectory();
        $this->ctrl->redirect($this, "uploadMultipleSubtitleFileForm");
    }

    /**
     * Save selected srt files as new srt files
     */
    public function saveMultiSrt(): void
    {
        $cnt = $this->multi_srt->moveMultiSrtFiles();
        $this->multi_srt->clearMultiSrtDirectory();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("cont_moved_srt_files") . " (" . $cnt . ")", true);
        $this->ctrl->redirect($this, "uploadMultipleSubtitleFileForm");
    }
}
