<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Upload SRT files to all media objects of a learning module
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMMultiSrtUploadGUI
{
	protected $lm;
	public $multi_srt;

	/**
	 * Constructor
	 *
	 * @param ilObjLearningModule $a_lm learning module object
	 */
	public function __construct(ilObjLearningModule $a_lm)
	{
		global $ilCtrl;

		$this->ctrl = $ilCtrl;
		$this->lm = $a_lm;
		include_once("./Modules/LearningModule/classes/class.ilLMMultiSrt.php");
		$this->multi_srt = new ilLMMultiSrt($this->lm);
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd("uploadMultipleSubtitleFileForm");

		if (in_array($cmd, array("uploadMultipleSubtitleFileForm", "uploadMultipleSubtitleFile", "showMultiSubtitleConfirmationTable", "cancelMultiSrt", "saveMultiSrt")))
		{
			$this->$cmd();
		}
	}

	/**
	 *	Upload multiple stubtitles
	 */
	function uploadMultipleSubtitleFileForm()
	{
		global $ilToolbar, $lng, $ilCtrl;

		ilUtil::sendInfo($lng->txt("cont_upload_multi_srt_howto"));

		// upload file
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this), true);
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($lng->txt("cont_subtitle_file")." (.zip)", "subtitle_file");
		$fi->setSuffixes(array("zip"));
		$ilToolbar->addInputItem($fi, true);

		$ilToolbar->addFormButton($lng->txt("upload"), "uploadMultipleSubtitleFile");
	}

	/**
	 * Upload multiple subtitles
	 */
	function uploadMultipleSubtitleFile()
	{
		try
		{
			$this->multi_srt->uploadMultipleSubtitleFile(ilUtil::stripSlashesArray($_FILES["subtitle_file"]));
			$this->ctrl->redirect($this, "showMultiSubtitleConfirmationTable");
		}
		catch (ilLMException $e)
		{
			ilUtil::sendFailure($e->getMessage(), true);
			$this->ctrl->redirect($this, "uploadMultipleSubtitleFileForm");
		}

	}

	/**
	 * List of srt files in zip file
	 */
	function showMultiSubtitleConfirmationTable()
	{
		global $tpl;

		include_once("./Modules/LearningModule/classes/class.ilLMMultiSrtConfirmationTable2GUI.php");
		$tab = new ilLMMultiSrtConfirmationTable2GUI($this, "showMultiSubtitleConfirmationTable");
		$tpl->setContent($tab->getHTML());
	}

	/**
	 * Cancel Multi Feedback
	 */
	function cancelMultiSrt()
	{
		$this->multi_srt->clearMultiSrtDirectory();
		$this->ctrl->redirect($this, "uploadMultipleSubtitleFileForm");
	}

	/**
	 * Save selected srt files as new srt files
	 */
	function saveMultiSrt()
	{
		global $ilCtrl, $lng;

		$cnt = $this->multi_srt->moveMultiSrtFiles();
		$this->multi_srt->clearMultiSrtDirectory();

		ilUtil::sendSuccess($lng->txt("cont_moved_srt_files")." (".$cnt.")", true);
		$ilCtrl->redirect($this, "uploadMultipleSubtitleFileForm");
	}



}

?>