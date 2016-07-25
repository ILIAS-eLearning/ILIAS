<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once("./Services/Certificate/classes/class.ilCertificate.php");

/**
* GUI class to create PDF certificates
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup Services
*/
class ilCertificateGUI
{
	/**
	 * ilCertificate object reference
	 * @var ilCertificate
	 */
	protected $object;

	/**
	* The reference to the ILIAS control class
	*
	* @var object
	*/
	protected $ctrl;

	/**
	* The reference to the ILIAS tree class
	*
	* @var object
	*/
	protected $tree;

	/**
	* The reference to the ILIAS class
	*
	* @var object
	*/
	protected $ilias;

	/**
	* The reference to the Template class
	*
	* @var object
	*/
	protected $tpl;

	/**
	* The reference to the Language class
	*
	* @var object
	*/
	protected $lng;
	
	/**
	* The reference ID of the object
	*
	* @var object
	*/
	protected $ref_id;

	/**
	 * @var ilAccessHandler
	 */
	protected $acccess;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * ilCertificateGUI constructor
	 * @param ilCertificateAdapter $adapter A reference to the test container object
	 * @access public
	 */
	public function __construct(ilCertificateAdapter $adapter)
	{
		global $DIC;

		include_once "./Services/Certificate/classes/class.ilCertificate.php";
		$this->object = new ilCertificate($adapter);

		$this->lng     = $DIC['lng'];
		$this->tpl     = $DIC['tpl'];
		$this->ctrl    = $DIC['ilCtrl'];
		$this->ilias   = $DIC['ilias'];
		$this->tree    = $DIC['tree'];
		$this->tree    = $DIC['tree'];
		$this->acccess = $DIC['ilAccess'];
		$this->toolbar = $DIC['ilToolbar'];

		$this->ref_id = (int)$_GET['ref_id'];

		$this->lng->loadLanguageModule('certificate');
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$ret = $this->$cmd();
				break;
		}
		return $ret;
	}

	/**
	* Retrieves the ilCtrl command
	*/
	public function getCommand($cmd)
	{
		return $cmd;
	}
	
	/**
	* Import a certificate from a ZIP archive
	*/
	public function certificateImport()
	{
		$this->certificateEditor();
	}
	
	/**
	* Creates a certificate preview
	*/
	public function certificatePreview()
	{
		$this->object->createPreview();
	}

	/**
	* Exports the certificate
	*/
	public function certificateExportFO()
	{
		$this->object->deliverExportFileXML();
	}

	/**
	* Removes the background image of a certificate
	*/
	public function certificateRemoveBackground()
	{
		$this->object->deleteBackgroundImage();
		$this->certificateEditor();
	}
	
	/**
	* Deletes the certificate and all its data
	*/
	public function certificateDelete()
	{		
		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this, "certificateEditor"));
		$cgui->setHeaderText($this->lng->txt("certificate_confirm_deletion_text"));
		$cgui->setCancel($this->lng->txt("no"), "certificateEditor");
		$cgui->setConfirm($this->lng->txt("yes"), "certificateDeleteConfirm");
		
		$this->tpl->setContent($cgui->getHTML());
	}
	
	/**
	* Deletes the certificate and all its data
	*/
	public function certificateDeleteConfirm()
	{
		$this->object->deleteCertificate();
		$this->ctrl->redirect($this, "certificateEditor");
	}
	
	/**
	* Saves the certificate
	*/
	function certificateSave()
	{
		$this->certificateEditor();
	}

	/**
	* Uploads the certificate
	*/
	public function certificateUpload()
	{
		$this->certificateEditor();
	}
	
	/**
	* Get the form values from an HTTP POST
	*/
	protected function getFormFieldsFromPOST()
	{
		$form_fields = array(
			"pageformat" => ilUtil::stripSlashes($_POST["pageformat"]),
			"margin_body_top" => ilUtil::stripSlashes($_POST["margin_body"]["top"]),
			"margin_body_right" => ilUtil::stripSlashes($_POST["margin_body"]["right"]),
			"margin_body_bottom" => ilUtil::stripSlashes($_POST["margin_body"]["bottom"]),
			"margin_body_left" => ilUtil::stripSlashes($_POST["margin_body"]["left"]),
			"certificate_text" => ilUtil::stripSlashes($_POST["certificate_text"], FALSE),
			"pageheight" => ilUtil::stripSlashes($_POST["pageheight"]),
			"pagewidth" => ilUtil::stripSlashes($_POST["pagewidth"]),
			"active" => ilUtil::stripSlashes($_POST["active"])
		);
		$this->object->getAdapter()->addFormFieldsFromPOST($form_fields);
		return $form_fields;
	}
	
	/**
	* Get the form values from the certificate xsl-fo
	*/
	protected function getFormFieldsFromFO()
	{		
		$form_fields = $this->object->getFormFieldsFromFO();
		$form_fields["active"] = $this->object->readActive();
		$this->object->getAdapter()->addFormFieldsFromObject($form_fields);
		return $form_fields;
	}

	/**
	* Shows the certificate editor for ILIAS tests
	*/
	public function certificateEditor()
	{
		if(strcmp($this->ctrl->getCmd(), "certificateSave") == 0)
		{
			$form_fields = $this->getFormFieldsFromPOST();
		}
		else
		{
			$form_fields = $this->getFormFieldsFromFO();
		}
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setPreventDoubleSubmission(false);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("certificate_edit"));
		$form->setMultipart(TRUE);
		$form->setTableWidth("100%");
		$form->setId("certificate");
		
		$active = new ilCheckboxInputGUI($this->lng->txt("active"), "active");
		$active->setChecked($form_fields["active"]);
		$form->addItem($active);

		$import = new ilFileInputGUI($this->lng->txt("import"), "certificate_import");
		$import->setRequired(FALSE);
		$import->setSuffixes(array("zip"));
		// handle the certificate import
		if (strlen($_FILES["certificate_import"]["name"]))
		{
			if ($import->checkInput())
			{
				$result = $this->object->importCertificate($_FILES["certificate_import"]["tmp_name"], $_FILES["certificate_import"]["name"]);
				if ($result == FALSE)
				{
					$import->setAlert($this->lng->txt("certificate_error_import"));
				}
				else
				{
					$this->ctrl->redirect($this, "certificateEditor");
				}
			}
		}
		$form->addItem($import);

		$pageformat  = new ilRadioGroupInputGUI($this->lng->txt("certificate_page_format"), "pageformat");
		$pageformats = $this->object->getPageFormats();
		$pageformat->setValue($form_fields["pageformat"]);
		foreach($pageformats as $format)
		{
			$option = new ilRadioOption($format["name"], $format["value"]);
			if(strcmp($format["value"], "custom") == 0)
			{
				$pageheight = new ilTextInputGUI($this->lng->txt("certificate_pageheight"), "pageheight");
				$pageheight->setValue($form_fields["pageheight"]);
				$pageheight->setSize(6);
				$pageheight->setValidationRegexp("/[0123456789\\.](cm|mm|in|pt|pc|px|em)/is");
				$pageheight->setInfo($this->lng->txt("certificate_unit_description"));
				$pageheight->setRequired(true);
				$option->addSubitem($pageheight);

				$pagewidth = new ilTextInputGUI($this->lng->txt("certificate_pagewidth"), "pagewidth");
				$pagewidth->setValue($form_fields["pagewidth"]);
				$pagewidth->setSize(6);
				$pagewidth->setValidationRegexp("/[0123456789\\.](cm|mm|in|pt|pc|px|em)/is");
				$pagewidth->setInfo($this->lng->txt("certificate_unit_description"));
				$pagewidth->setRequired(true);
				$option->addSubitem($pagewidth);
			}
			$pageformat->addOption($option);
		}
		$pageformat->setRequired(true);
		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0) $pageformat->checkInput();
		
		$form->addItem($pageformat);

		$bgimage = new ilImageFileInputGUI($this->lng->txt("certificate_background_image"), "background");
		$bgimage->setRequired(FALSE);
		$bgimage->setUseCache(false);
		if (count($_POST)) 
		{
			// handle the background upload
			if (strlen($_FILES["background"]["tmp_name"]))
			{
				if ($bgimage->checkInput())
				{
					$result = $this->object->uploadBackgroundImage($_FILES["background"]["tmp_name"]);
					if ($result == FALSE)
					{
						$bgimage->setAlert($this->lng->txt("certificate_error_upload_bgimage"));
					}
				}
			}
		}
		if (!$this->object->hasBackgroundImage())
		{
			include_once "./Services/Certificate/classes/class.ilObjCertificateSettingsAccess.php";
			if (ilObjCertificateSettingsAccess::hasBackgroundImage())
			{
				$bgimage->setImage(ilObjCertificateSettingsAccess::getBackgroundImageThumbPathWeb());
			}
		}
		else
		{
			$bgimage->setImage($this->object->getBackgroundImageThumbPathWeb());
		}
		$form->addItem($bgimage);

		$rect = new ilCSSRectInputGUI($this->lng->txt("certificate_margin_body"), "margin_body");
		$rect->setRequired(TRUE);
		$rect->setUseUnits(TRUE);
		$rect->setTop($form_fields["margin_body_top"]);
		$rect->setBottom($form_fields["margin_body_bottom"]);
		$rect->setLeft($form_fields["margin_body_left"]);
		$rect->setRight($form_fields["margin_body_right"]);
		$rect->setInfo($this->lng->txt("certificate_unit_description"));
		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0) $rect->checkInput();
		$form->addItem($rect);
		
		$certificate = new ilTextAreaInputGUI($this->lng->txt("certificate_text"), "certificate_text");
		$certificate->removePlugin('ilimgupload');
		$certificate->setValue($form_fields["certificate_text"]);
		$certificate->setRequired(TRUE);
		$certificate->setRows(20);
		$certificate->setCols(80);
		
		// fraunhpatch start
		$common_desc_tpl = new ilTemplate("tpl.common_desc.html", true, true, "Services/Certificate");
		foreach (ilCertificate::getCustomCertificateFields() as $f)
		{
			$common_desc_tpl->setCurrentBlock("cert_field");
			$common_desc_tpl->setVariable("PH", $f["ph"]);
			$common_desc_tpl->setVariable("PH_TXT", $f["name"]);
			$common_desc_tpl->parseCurrentBlock();
		}
		$common_desc = $common_desc_tpl->get();
		// fraunhpatch start
		
		$certificate->setInfo($this->object->getAdapter()->getCertificateVariablesDescription().$common_desc);
		$certificate->setUseRte(TRUE, '3.4.7');
		$tags = array(
		"br",
		"em",
		"font",
		"li",
		"ol",
		"p",
		"span",
		"strong",
		"u",
		"ul"	
		);
		$certificate->setRteTags($tags);
		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0) $certificate->checkInput();
		$form->addItem($certificate);

		$this->object->getAdapter()->addAdditionalFormElements($form, $form_fields);

		if($this->acccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			if ($this->object->isComplete() || $this->object->hasBackgroundImage())
			{
				$this->toolbar->setFormAction($this->ctrl->getFormAction($this));

				require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
				$preview = ilSubmitButton::getInstance();
				$preview->setCaption('certificate_preview');
				$preview->setCommand('certificatePreview');
				$this->toolbar->addStickyItem($preview);

				$export = ilSubmitButton::getInstance();
				$export->setCaption('certificate_export');
				$export->setCommand('certificateExportFO');
				$this->toolbar->addButtonInstance($export);

				$delete = ilSubmitButton::getInstance();
				$delete->setCaption('delete');
				$delete->setCommand('certificateDelete');
				$this->toolbar->addButtonInstance($delete);
			}
			$form->addCommandButton("certificateSave", $this->lng->txt("save"));
		}

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());

		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0)
		{
			if ($_POST["background_delete"])
			{
				$this->object->deleteBackgroundImage();
			}
			if ($form->checkInput())
			{
				try
				{
					$xslfo = $this->object->processXHTML2FO($form_fields);
					$this->object->getAdapter()->saveFormFields($form_fields);
					$this->object->saveCertificate($xslfo);					
					$this->object->writeActive($form_fields["active"]);					
					ilUtil::sendSuccess($this->lng->txt("saved_successfully"), TRUE);
					$this->ctrl->redirect($this, "certificateEditor");
				}
				catch (Exception $e)
				{
					ilUtil::sendFailure($e->getMessage());
				}
			}
		}
	}
}
